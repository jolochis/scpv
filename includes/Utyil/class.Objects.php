<?php
	class Objects{
		private $ac =0;
		private $sc =0;
		private $objects=array();
		private $pattern = array('/[A-Z]/','/\(/','/\)/');
		//private $states=array();
		private $configurations=array();
		private $utyl;
		
		public function addDirection($id,$to){
			if(!key_exists("d{$id}",$this->objects)){
				$this->objects["d{$id}"]=array("type"=>"direction","id"=>"d{$id}","pos"=>"#","configurations"=>array());
				$this->addDirConfiguration("d{$id}",$id,$to);
			}
			else
				$this->addDirConfiguration("d{$id}",$id,$to);
				
		}
		
		public function addObject($id,$type,$pos,$value,$points,$to){
			if(!key_exists("{$id}",$this->objects) && $type!="direction")
				$this->objects["{$id}"]=array(
					"type"=>$type,
					"id"=>$id,
					"attr"=>trim($to),
					"pos"=>$pos,
					"points"=>$points,
					"configurations"=>array(),
					"directions"=>array());
			else if($type=="direction")
				$this->addDirection($id,$to);
		}
		
		private function addDirConfiguration($id,$id2,$to){
			if(!key_exists("{$to}",$this->objects["{$id}"]["configurations"]))
				$this->objects["{$id}"]["configurations"]["{$to}"]=array(
				"id"=>"cfg{$id2}",
				"init"=>'0',
				"end"=>'$',
				"attr1"=>$to,
				"states"=>array());
		}
		
		public function addConfiguration($oid,$id,$init,$end,$attr){
			if(!key_exists("cfg{$id}",$this->objects["{$oid}"]["configurations"]))
				$this->objects["{$oid}"]["configurations"]["cfg{$id}"]=array(
				"rid"=>"{$id}",
				"id"=>"cfg{$id}",
				"init"=>trim($init),
				"end"=>trim($end),
				"attr1"=>trim($attr),
				"states"=>array());
		}
		
		
		public function addState($oid,$id,$at1,$at2,$at3){			
			if(key_exists("cfg{$id}",$this->objects["{$oid}"]["configurations"])){
				$t = sizeof($this->objects["{$oid}"]["configurations"]["cfg{$id}"]["states"])+1;
				$this->objects["{$oid}"]["configurations"]["cfg{$id}"]["states"]["attr{$t}"]=
					array(
						"attr1"=>trim($at1),
						"attr2"=>trim($at2),
						"attr3"=>trim($at3));
			}
		}
		
		
		public function getObjects(){
			return $this->objects;
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

