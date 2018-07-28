<?php
	require_once("class.Segments.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "/WebSCPVv1/includes/condb.php";
	class Utyil{
		private $utyl;
		private $segmentos = array();
		private $propuesta;
		
		function Utyil($p){
			$this->propuesta = $p;
		}
		
		private function addObject($segment,$points,$to){
			global $condb;
			$qry="Select * from \"getObjetos\"('{$points}'::text,{$this->propuesta}) ";
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
		
		public function addSegment($id,$distancia,$ancho,$coordenadas4,$coordenadas9,$type,$father){
			
		
			$segment = new Segments();
			$segment->createSegment($id,$distancia,0,$coordenadas4,$coordenadas9,$type);
			//Revisar la creación de objetos ---->>> no se genera IMPORTANTE
			$segment->createObjects($coordenadas9, $this->propuesta,$id);
			$segment->createConnections($id,$this->propuesta);
			if(isset($this->segmentos["{$father}"])){				
				$this->segmentos["{$father}"]["{$id}"]=$segment;
			}
			else{								
				$this->segmentos["{$father}"] = array();
				$this->segmentos["{$father}"]["{$id}"]=$segment;
			} 
		}
		
		public function getSegments(){
			return $this->segmentos;
		}
		
		public function createUtyil(){
		//echo var_dump($this->segmentos);
		
			foreach($this->segmentos as $segmento => $segment)
			{
				//var_dump($object);
				//$this->utyl.="<segments name='{$segmento}'>";
				foreach($segment as $s => $object){					
					$object->createUtyil();
					$this->utyl.=$object->getUtyil();
				}
				//$this->utyl.="</segments>";
				//echo $this->utyl="<segments>".$object->getUtyil()."</segments>";
			}

		}
		
		public function getUtyil(){
			return $this->utyl;
		}
	}
?>
