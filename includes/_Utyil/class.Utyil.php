<?php
	require_once("class.Segments.php");
	require_once("condb.php");
	class Utyil{
		private $utyl;
		private $segmentos = array();
		private $propuesta;
		
		function Utyil($p){
			$this->propuesta = $p;
		}
		
		private function addObject($segment,$points,$to){
			global $condb;
			$qry="Select * from \"getObjetos\"(cast('{$points}' as Text),{$this->propuesta}) ";
			// $qry;
			//	echo $qry;
			file_put_contents("consultas",$qry."\n",FILE_APPEND);
			$consult = pg_query($condb,$qry);
			if($consult){
				//echo var_dump($qry);
				while($obj = pg_fetch_array($consult)){
					$segment->addObject($obj['_name'],$obj['_type'],0.5,"",$obj['_points'],$to);
					if($obj['_type']!="tope")
						$segment->addConfiguration($obj['_name'],$obj['_cid'],$obj['_init'],$obj['_end'],$obj['_attr']);
					if($obj['_type']=="semaforo"||$obj['_type']=="semaphore")
						$segment->addState($obj['_name'],$obj['_sid'],$obj['_s1'],$obj['_s2'],$obj['_s3']);
				}
			}
		}
		
		public function addSegment($id,$distancia,$ancho,$coordenadas4,$coordenadas9,$from,$to,$attr){
			//echo ($id." con ".$to."\n");
			$valid = false;
			if(!key_exists("{$id}",$this->segmentos))
				$valid = true;
			if(key_exists("{$id}",$this->segmentos) && strlen($to)>0)
				$valid = true;
			if($attr=="pozo") $to =null;
			
			if($valid){
				//echo var_dump($this->segmentos);
				$segment = new Segments();
				$segment->createSegment($id,$distancia,0,$coordenadas4,$coordenadas9,$attr);
				$this->addObject($segment,$coordenadas9,$to);
				//$segment->addObject("ejemplo",12.3,"ninguno","Objeto","1 2,4 6");
				
				$segment->addObject($id,"direction",0.5,"#","#",$to);
				$segment->addConnection(0,$from,$to);
				$this->segmentos ["{$id}"]=$segment;
			}
			else{
				
				$segment=$this->segmentos["{$id}"];
				$this->addObject($segment,$coordenadas9,$to);
				//$segment->addObject("ejemplo1",12.3,"ninguno1","Objeto1","1 2,4 6");
				$segment->addConnection(0,$from,$to);
			}
			
		}
		
		public function createUtyil(){
		//echo var_dump($this->segmentos);
			foreach($this->segmentos as $segmento => $object)
			{
					//var_dump($object);
					$object->createUtyil();
					$this->utyl.=$object->getUtyil();
					//echo $this->utyl="<segments>".$object->getUtyil()."</segments>";
			}

		}
		
		public function getUtyil(){
			return $this->utyl;
		}
	}
?>
