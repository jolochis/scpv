<?php

class Connections{
	private $connection = array();
	private $utyl;
	global $condb;
	
	function __construct($segment, $idp){
		global $condb;
		$query = "Select \"segmentConnection\",\"connectionType\" from \"segmentConnections\" WHERE \"idPropuesta\"={$idp} AND \"segmentName\" like {$segment}
			UNION Select \"segmentName\",\"connectionType\" from \"segmentConnections\" WHERE \"idPropuesta\"={$idp} AND \"segmentConnection\" like {$segment}";
		$consult = pg_query($condb,$query);
		if($consult){
			$row = pg_fetch_array($consult);
			addConnection($row[0],$row[1]);
		}
	
	}
	
	public function addConnection($to, $type){
		if(!key_exists("{$to}",$this->connection)
			$this->connection["{$to}"] = array("type" =>$type, "to"=>$to);
	}
	
	public function createUtyil(){
	
		$this->utyl= "<connection> ";
		foreach($this->connection as $con => $value){
			$this->utyl.="<c type=\"{$value['type']}\" to =\"{$value['to']}\"/>\n";
		}
		$this->utyl.=" </connection>\n";
	}
	
	public function getUtyil(){
		return $this->utyl;
	}
	
}
?>