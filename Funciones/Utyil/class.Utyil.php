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
		
		public function addSegment($id,$distancia,$ancho,$coordenadas4,$coordenadas9,$type){
			
				$segment = new Segments();
				$segment->createSegment($id,$distancia,0,$coordenadas4,$coordenadas9,$type);
				$segment->createObjects($this->propuesta, $coordenadas9);
				$segment->createConnections($id,$this->propuesta);
				
				$this->segmentos ["{$id}"]=$segment;
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
