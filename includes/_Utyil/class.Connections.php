<?php
	class Connections{
		private $connection= array();
		private $utyl;
		
		public function addConnection($type,$from,$to){
			//modificar para agregar las segundas direcciones
			if(is_numeric($type) && (!empty($from) || !empty($to)))
			{
				if($type>0)
					$tipo="NS";
				else
					$tipo="NC";
				if(!key_exists("{$to}",$this->connection) && !empty($to))
					$this->connection["{$to}"]=array("connection"=>$tipo,"to"=>trim($to));
				if(!key_exists("{$from}",$this->connection) && !empty($from))
					$this->connection["{$from}"]=array("connection"=>$tipo,"to"=>trim($from));
				
			}
		}
		
		public function createUtyil(){
			$this->utyl= "<conection> ";
			
			foreach($this->connection as $con=> $value){
				$this->utyl.="<c type=\"{$value['connection']}\" to =\"{$value['to']}\"/>\n";
			}
			$this->utyl.=" </conection>\n";
		}
		
		public function getUtyil(){
			return $this->utyl;
		}
	}
?>