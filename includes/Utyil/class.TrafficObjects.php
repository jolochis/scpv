<?php

	include_once $_SERVER['DOCUMENT_ROOT'] . "/WebSCPVv1/includes/condb.php";
	class TrafficObjects{
		
		private $objects = array();
		private $connections;
		private $utyl;
		private $pattern = array('/[A-Z]/','/\(/','/\)/');
		private $idp;
		
		function __construct(){
			
		}
		
		public function createObjects($coordinates, $idp,$segment){
			global $condb;
			$this->idp=$idp;
			$query = "SELECT * FROM \"getObjetos\"(cast('{$coordinates}' as Text), {$idp})";
			$consult = pg_query($condb,$query);
			
			if($consult){
				while($row = pg_fetch_array($consult))
					$this->addObjects($row,$segment);
			}
			
			$query = "SELECT c.attr from configuracion c, objetos o where c.idobject = o.id 
				AND o.segment like '{$segment}' AND o.idprop = {$idp}";
			$consult = pg_query($condb,$query);
			
			if($consult){
				while($row = pg_fetch_array($consult))
					$this->addDirection($segment,$row[0]);
			}
		}
		
		public function createObjectsFromProposal($idp, $idu){
			global $condb;
			$query = "SELECT * FROM \"getObjetos\"({$idp}, {$idu})";
			$consult = pg_query($condb,$query);
			
			if($consult){
				while($row = pg_fetch_array($consult))
					$this->addObjects($row);
			}
		}
		
		
		private function addObjects($obj,$segment){
		/*Esta funcion debe implementarce despues de que se asocie la creación de objetos con 
		un determinado segmento*/
			if($obj["_type"]=="direction" ){
				$this->addDirection($segment,$obj["_attr"]);
			}
			else{
				if(!key_exists("{$obj['_name']}",$this->objects))
				{
					/*Modificar, se debe de agregar la possición del objeto en el sgmento*/
					$this->objects["{$obj['_name']}"] = array(
						"type"=>$obj["_type"],
						"attr"=>$obj["_attr"],
						"pos"=>$obj["_pos"],
						"id"=>$obj["_name"],
						"points"=>$obj["_points"],
						"configurations"=>array()
					);
				}
				if($obj['_type']!="tope"){
				   
					$this->addObjectConfiguration($obj['_name'],$obj['_cid'],$obj['_init'],$obj['_end'],$obj['_attr']);
					if($obj["_type"]=="semaforo")
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
			global $condb;
			if(!key_exists("cfg{$id}",$this->objects["{$oid}"]["configurations"])){
				$this->objects["{$oid}"]["configurations"]["cfg{$id}"]=array(
				"rid"=>"{$id}",
				"id"=>"cfg{$id}",
				"init"=>trim($init),
				"end"=>trim($end),
				"attr1"=>trim($attr),
				"states"=>array());
				//echo var_dump($this->objects["{$oid}"]);
				
				if($this->objects["{$oid}"]["type"]=="aforo"){
					
					$query = "SELECT s.\"segmentName\" FROM segments s, objetos o
						WHERE o.id = {$oid} AND s.\"segmentName\" like 'S%' AND s.\"idPropuesta\" = {$this->idp}  AND
						ST_INTERSECTS(St_asText(St_endPoint(o.positiondst))::geometry,ST_Buffer(coordinates::geometry,4))";
					
					if($result = pg_query($condb,$query)){
						
						$row = pg_fetch_array($result);
						//echo var_dump($row[0]);
						$this->objects["{$oid}"]["configurations"]["cfg{$id}"]["attr2"] = $row[0];
					}
					//echo var_dump($this->objects["{$oid}"]);
				}
			}
		}
		
		public function addDirection($segment, $to){
			if(!key_exists("d{$segment}", $this->objects)){
				$this->objects["d{$segment}"] = array("type"=>"direction", 
					"pos"=>"#", 
					"id"=>"d{$segment}",
					"configurations" =>array());
					
				$this->objects["d{$segment}"]["configurations"][$to] = array(
						"init" => 0,
						"end" => "$",
						"attr1" => $to,
						"id" => "cgf{$to}",
						"states" => array()
					);
			}
			else{
				if(!key_exists($to, $this->objects["d{$segment}"]["configurations"]))
					$this->objects["d{$segment}"]["configurations"][$to] = array(
						"init" => 0,
						"end" => "$",
						"attr1" => $to,
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
						case "aforo":$type="flow";$id="F{$value['id']}";$pos="#"; break; //$this->ac++;
						case "semaforo":$type="semaphore";$id="se{$value['id']}"; break; //$this->sc++;
						case "tope":$type="bump";$id="bu{$value['id']}";break;
					}
					/*Borrar despues
					if($type=="flow" && $this->ac>1)
						continue;
					
					if($type=="semaphore" && $this->sc>1)
						continue;
					BorrarDespues*/
					
					$this->utyl.="<object id=\"{$id}\" position=\"{$pos}\" type=\"{$type}\"";
					$this->utyl.=!empty($value['points']) ? " points=\"".preg_replace($this->pattern,'',$value['points'])."\"" : " ";
					
					$this->utyl.=">";
					if(sizeof($value["configurations"]>0))
					{
						$this->utyl.="<configurations>";
						foreach($value["configurations"] as $config=>$con){
							$this->utyl.="<configuration id =\"{$con['id']}\" init=\"{$con['init']}\" end=\"{$con['end']}\"";
							if($type=="flow" || $type=="direction"){
								$this->utyl.= !empty($con['attr1']) ? " attr1='{$con['attr1']}'\n " : " ";
								$this->utyl.= !empty($con['attr2']) ? " attr2='{$con['attr2']}'\n " : " ";
							}
							$this->utyl.=">";
						//file_put_contents("errores.txt", var_dump($con['states'])."\n",FILE_APPEND);
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