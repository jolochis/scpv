<?php

class Connections{
	private $connection = array();
	private $utyl;
	
	function __construct(){
	
	}
	
	public function createConnections($segment, $idp){
		global $condb;
		$query = "Select \"segmentConnection\",\"connectionType\" from \"segmentConnections\" WHERE \"idPropuesta\"={$idp} AND \"segmentName\" like '{$segment}'
			UNION Select \"segmentName\",\"connectionType\" from \"segmentConnections\" WHERE \"idPropuesta\"={$idp} AND \"segmentConnection\" like '{$segment}'";
		$consult = pg_query($condb,$query);
		file_put_contents("consultas",$query."\n",FILE_APPEND);
		if($consult){
			while($row = pg_fetch_array($consult)){
				file_put_contents("consultas",$row[0]."-".$row[1]."\n",FILE_APPEND);
				$this->addConnection($row[0],$row[1]);
			}
		}
	}
	
	public function addConnection($to, $type){
		if(!key_exists("{$to}",$this->connection))
			$this->connection["{$to}"] = array("type" =>$type, "to"=>$to);
	}
	
	public function createUtyil(){
	
		if(sizeof($this->connection)>0){
			$this->utyl= "<connection> ";
			foreach($this->connection as $con => $value){
				$this->utyl.="<c type=\"{$value['type']}\" to =\"{$value['to']}\"/>\n";
			}
			$this->utyl.=" </connection>\n";
		}
	}
	
	public function getUtyil(){
		return $this->utyl;
	}
	
}
?>