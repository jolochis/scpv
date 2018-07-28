<?php
	require_once("class.Segments.php");
	require_once("class.Objects.php");
	require_once("class.Connections.php");
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
		private $attr;
		
		function __construct(){
			$this->conecciones = new Connections();
			$this->objetos = new Objects();
		}
		
		public function addConfiguration($oid,$id,$init,$end,$attr){
			$this->objetos->addConfiguration($oid,$id,$init,$end,$attr);
		}
		
		public function addState($oid,$id,$at1,$at2,$at3){
			$this->objetos->addState($oid,$id,$at1,$at2,$at3);
		}
		
		public function createSegment($id,$distancia,$ancho,$coordenadas4,$coordenadas9,$attr){
			$this->distancia=$distancia;
			$this->ancho=$ancho;
			$this->coordenadas = preg_replace($this->pattern,'',$coordenadas4);
			$this->coordenadas9 = preg_replace($this->pattern,'',$coordenadas9);
			$this->id=$id;
			$this->type= $id[0]=="I" ? "itersection" : "segment";
			$this->attr=$attr;
		}
		
		public function addObject($type,$pos,$value,$id,$points,$to){
			$this->objetos->addObject($type,$pos,$value,$id,$points,$to);
		}
		
		public function addConnection($type,$from,$to){
			
			$this->conecciones->addConnection($type,$from,$to);
		}
		
		public function createUtyil(){
		
			//if(!empty($this->id) && !empty($this->distancia) && !empty($this->type) && !empty($this->coordenadas))
			//{
				$this->utyl.="<segment id=\"{$this->id}\" length=\"{$this->distancia}\" type=\"{$this->type}\" points=\"{$this->coordenadas}\" attr=\"{$this->attr}\">";
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
