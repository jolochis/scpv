<?php

	class TrafficObjects{
		//global $condb;
		private $objects = array();
		private $connections;
		
		function TrafficObjects($coordinates, $idp){
			
			$query = "SELECT * FROM getObjetos('{$coordinates}', $idp)";
			$consult = pg_query($condb,$query);
			
			if($consult){
				$row = pg_fetch_array($consult);
				foreach($row as $attr)
					$this->addObjects($attr);
			}
			
		}
		
		
		function addObjects($obj){
		/*Esta funcion debe implementarce despues de que se asocie la creación de objetos con 
		un determinado segmento*/
			if($obj["_type"]=="direction"){
				$this->addDirection($obj["_name"],$obj["_attr"]);
			}
			else{
				if(!key_exixts("{$obj['_name']}",$this->objects))
				{
					/*Modificar, se debe de agregar la possición del objeto en el sgmento*/
					$this->objects["{$obj['_name']}"] = array(
						"type"=>$obj["_type"],
						"attr"=>$obj["_attr"],
						"pos"=>0.5,
						"id"=>$obj["_name"],
						"points"=>$obj["_points"],
						"configurations"=>array()
					);
				}
				if($obj['_type']!="tope"){
				   
					$this->addObjectConfiguration($obj['_name'],$obj['_cid'],$obj['_init'],$obj['_end'],$obj['_attr']);
					if($obj["_type"]=="semaphore")
						$this->addConfigurationState($obj['_name'],$obj['_sid'],$obj['_s1'],$obj['_s2'],$obj['_s3']);
				}
			}
		}
		
		public function addConfigurationState($oid,$id,$at1,$at2,$at3){			
			if(key_exists("cfg{$id}",$this->objects["{$oid}"]["configurations"])){
				$t = sizeof($this->objects["{$oid}"]["configurations"]["cfg{$id}"]["states"])+1;
				$this->objects["{$oid}"]["configurations"]["cfg{$id}"]["states"]["attr{$t}"]=
					array(
						"attr1"=>trim($at1),
						"attr2"=>trim($at2),
						"attr3"=>trim($at3)
					);
			}
		}
		
		public function addObjectConfiguration($oid,$id,$init,$end,$attr){
			if(!key_exists("cfg{$id}",$this->objects["{$oid}"]["configurations"]))
				$this->objects["{$oid}"]["configurations"]["cfg{$id}"]=array(
				"rid"=>"{$id}",
				"id"=>"cfg{$id}",
				"init"=>trim($init),
				"end"=>trim($end),
				"attr1"=>trim($attr),
				"states"=>array());
		}
		
		public function addDirection($segment, $to){
			if(!key_exixts("d{$segment}", $this->objects)){
				$this->objects["d{$segment}"] = array("type"=>"direction", 
					"pos"=>"#", 
					"id"=>"d{$segment}",
					"configurations" =>array());
					
				$this->objects["d{$segment}"]["configurations"][$to] = array(
						"init" => 0,
						"end" => "$",
						"attr" => $to,
						"id" => "cgf{$to}",
						"states" => array()
					);
			}
			else{
				if(!key_exixts($to, $this->objects["d{$segment}"]["configurations"])
					$this->objects["d{$segment}"]["configurations"][$to] = array(
						"init" => 0,
						"end" => "$",
						"attr" => $to,
						"id" => "cfg{$to}",
						"states" => array()
					);
			}
		}
		
		
		public function createUtyil(){
			if(sizeof($this->objects)>0)
			{
				//$this->utyl="<objects> ";
				foreach($this->objects as $object => $value)
				{
					$id=$value['id'];
					$type=$value['type'];
					$pos = $value['pos'];
					
					switch($value['type']){
						case "aforo":$type="flow";$id="F{$value['id']}";$pos="#"; $this->ac++;break;
						case "semaforo":$type="semaphore";$id="se{$value['id']}"; $this->sc++;break;
						case "tope":$type="bump";$id="bu{$value['id']}";break;
					}
					//Borrar despues
					if($type=="flow" && $this->ac>1)
						continue;
					
					if($type=="semaphore" && $this->sc>1)
						continue;
					//BorrarDespues
					
					$this->utyl.="<object id=\"{$id}\" position=\"{$pos}\" type=\"{$type}\"";
					$this->utyl.=!empty($value['points']) ? " points=\"".preg_replace($this->pattern,'',$value['points'])."\"" : " ";
					
					$this->utyl.=">";
					if(sizeof($value["configurations"]>0))
					{
						$this->utyl.="<configurations>";
						foreach($value["configurations"] as $config=>$con){
							$this->utyl.="<configuration id =\"{$con['id']}\" init=\"{$con['init']}\" end=\"{$con['end']}\"";
							if($type=="flow" || $type=="direction")
								$this->utyl.= !empty($con['attr1']) ? " attr1='{$con['attr1']}'\n " : " ";
							$this->utyl.=">";
						
							if(sizeof($con['states'])>0){
								foreach($con['states'] as $states=>$st){
									if($type=="flow")
										$this->utyl.="<state attr1=\"{$st['attr1']}\" attr2=\"{$st['attr2']}\" attr3=\"{$st['attr3']}\" />\n";
									else
										$this->utyl.="<state attr1=\"{$st['attr1']}\" attr2=\"{$st['attr2']}\" />\n";
								}
							}
							$this->utyl.="</configuration>";
						}
						$this->utyl.="</configurations>";
					}
					$this->utyl.=" </object>\n";
				}
				
			}
		}
		
		public function getUtyil(){
			return $this->utyl;
		}
	}
?>