<?php
	require_once("class.TrafficObjects.php");
	require_once("class.Connections.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "/WebSCPVv1/includes/condb.php";
	
	class Segments{
		private $segment;
		private $pattern = array('/[A-Z]/','/\(/','/\)/');
		private $distancia;
		private $conecciones;
		private $objetos;
		private $ancho;
		private $coordenadas;
		private $coordenadas9;
		private $id;
		private $type;
		private $utyl;
		private $propuesta;
		
		function __construct(){
	
		}
		
		public function createSegment($id,$distancia,$ancho,$coordenadas4,$coordenadas9,$type){
			$this->distancia=$type == "intersection" ? 5:$distancia;
			//Modificar poner ancho real a interseciones
			$this->ancho= $type == "intersection" ? 5:$ancho;
			$this->coordenadas = preg_replace($this->pattern,'',$coordenadas4);
			$this->coordenadas9 = preg_replace($this->pattern,'',$coordenadas9);
			$this->id=$id;
			$this->type= $type;
		}
		
		
		public function createConnections($segment, $idp){
			$this->conecciones = new Connections();
			$this->conecciones->createConnections($segment,$idp);
		}
		
		public function createObjects($coor, $idp,$segment){
			$this->objetos = new TrafficObjects();
			$this->objetos->createObjects($coor,$idp,$segment);
		}
		
		public function addConfiguration($oid,$id,$init,$end,$attr){
			$this->objetos->addConfiguration($oid,$id,$init,$end,$attr);
		}
		
		public function addState($oid,$id,$at1,$at2,$at3){
			$this->objetos->addState($oid,$id,$at1,$at2,$at3);
		}
		
		public function addObject($type,$pos,$value,$id,$points,$to){
			$this->objetos->addObject($type,$pos,$value,$id,$points,$to);
		}
		
		public function addConnection($to,$type){
			
			$this->conecciones->addConnection($to,$type);
		}
		
		public function getObjects(){
			return $this->objetos;
		}
		
		public function createUtyil(){
		
			//if(!empty($this->id) && !empty($this->distancia) && !empty($this->type) && !empty($this->coordenadas))
			//{
				$this->utyl.="<segment id=\"{$this->id}\" length=\"{$this->distancia}\" type=\"{$this->type}\" points=\"{$this->coordenadas}\">\n";
				$this->objetos->createUtyil();
				$this->utyl.=$this->objetos->getUtyil();
				$this->conecciones->createUtyil();
				$this->utyl.=$this->conecciones->getUtyil();
				$this->utyl.="</segment>\n";
			//}
		}
		
		public function getUtyil(){
			return $this->utyl;
		}
	}
?>