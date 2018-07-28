<?php
session_start();
error_reporting(E_ALL);
ini_set( 'display_errors','1');
header("Content-type: application/json");
require_once("includes/condb.php");
require_once("includes/class.Resultados.php");
require_once("includes/class.Lineas.php");
require_once("includes/Utyil/class.Utyil.php");
require_once("includes/Utyil/class.Objects.php");

$d=$_GET;
$action  = !empty($d["action"])? $d["action"] : "";
if(!empty($action)){
	$action();
}

	function conectionCurl($fields,$url){
		//url-ify the data for the POST
		$fields_string="";
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&'; 
			}
		rtrim($fields_string, '&');
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		//execute post
		$result = curl_exec($ch);
		//close connection
		curl_close($ch);
		return $result;
	}


	function getFlujo(){
		//if(isset($_SESSION['currentPropUser'])){
			$d = $_GET;
			$url = 'c8ac5fc81d324358a446e5b2d162d247.cloudapp.net/WebServiceSimulatorJC.asmx/Flujo';
			$fields = array(
				'usuario' => $_SESSION['currentPropUser'],
				'configuracion' => $_SESSION['currentProp'],
				'hinicial' => $d['ti'],
				'hfinal' => $d['tf'],
				'segmento' =>"{$d['s']}"
				);
			$res = conectionCurl($fields,$url);
			/*$json = array("m"=>"ok",
			"status"=> conectionCurl($fields,$url));
		/*}
		else
			$json=array("m"=>"error","status"=>"No se puede consultar la Simulacion");
		 */
		 echo $res;
	}


	function getDensidad(){
		//if(isset($_SESSION['currentPropUser'])){
			$d = $_GET;
			$url = 'http://c8ac5fc81d324358a446e5b2d162d247.cloudapp.net/WebServiceSimulatorJC.asmx/Densidad';
			$fields = array(
				'usuario' => $_SESSION['currentPropUser'],
				'configuracion' => $_SESSION['currentProp'],
				'hinicial' => $d['ti'],
				'hfinal' => $d['tf'],
				'segmento' => "{$d['s']}"
				);
				
			$res = conectionCurl($fields,$url);
			/*$json = array("m"=>"ok",
			"status"=> conectionCurl($fields,$url));
		/*}
		else
			$json=array("m"=>"error","status"=>"No se puede consultar la Simulacion");
		 */
		// echo json_decode($json);
		echo $res;
	}

	function sendPrueba(){
		$data=$_GET;
		$fields_string="";
		$file = file_get_contents("Simulaciones/utyil{$data['u']}-{$data['p']}.xml");
		if($file){
			$xml = $file;
			$xml=preg_replace(array('/\</'),'*',$xml);
			$xml=preg_replace('/\>/','%',$xml);
			echo $xml;

			$url = 'c8ac5fc81d324358a446e5b2d162d247.cloudapp.net/WebServiceSimulatorJC.asmx/ReceiveUtyl';
			$fields = array(
				'user' => 566,
				'config' => 817,
				'TiempoInicial' => 0,
				'TiempoFinal' => 10,
				'FileUtyl' => $xml
				);

			//url-ify the data for the POST
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');
			//open connection
			$ch = curl_init();
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			//curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
			//execute post
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			echo $result;
		}
		else
			echo json_encode(array("m"=>"error"));
	}

	function consultaSegmento(){

		$file =file_get_contents("http://?p=817&u=566&s={$_GET['s']}");
		echo json_encode($file);
	
	}

	function consultaSimulador(){
		$data=$_POST;
		$file = file_get_contents("Simulaciones/utyil{$data['u']}-{$data['p']}.xml");
		if($file){
			$simple = json_encode(simplexml_load_string($file));
			echo $simple;
		}
		else
			echo json_encode(array("mensaje"=>"Error"));
	}

	function splitFeatures($val,&$feature){
		$prop["id"] = $val['id'];
		$prop["type"] = $val['type'];
		foreach($val['configs'] as $f2 => $val2){
			$prop["config"][]= $val2;
			}
		
		$feature[] = array(
			"type"=>"feature",
			"properties" => $prop,
			"geometry" => (array)json_decode($val['points'])
			);
		//echo var_dump(json_encode($val));
	}

	function getObjects(){
		GLOBAL $condb;
		$d=$_GET;
		$objetos = new Objects();
		$aforos = array();
		$semaforos= array();
		$topes= array();

		$feature;
		if($d['p']!=0){
			$query="Select * FROM \"getObjetos\"({$d['p']},{$d['u']})";
			$rst = pg_query($condb,$query);
			if($rst){
				
				while($row = pg_fetch_array($rst)){					
					$objetos->addObject($row['_id'],$row['_type'],0,0,$row['_coor'],0);
					$objetos->addConfiguration($row['_id'],$row['_cid'],$row['_init'],$row['_end'],$row['_attr']);
					$objetos->addState($row['_id'],$row['_sid'],$row['_s1'],$row['_s2'],$row['_s3']);
					}
					
				$features = $objetos->getObjects();
				
				foreach($features as $f => $val){
					switch($val['type']){
						case "tope": 
							splitFeatures($val,$topes);break;
						case "semaforo": 
							splitFeatures($val,$semaforos);break;
						case "aforo": 
							splitFeatures($val,$aforos);break;
					}
				}
				$json = array(
					"m"=>"ok","status"=>"Cargando",
					"aforos" =>array("features"=>array(
						"type"=>"FeatureCollection",
						"features"=>$aforos
						)),
					"semaforos" => array("features"=>array(
						"type"=>"FeatureCollection",
						"features"=>$semaforos
						)),
					"topes" => array("features"=>array(
						"type"=>"FeatureCollection",
						"features"=>$topes
						))
					);
				}
			else{
				$json = array("m"=>"error","status"=>"No Hay informacion");
				}
		}
		else
		{$json = array("m"=>"error","status"=>"No Hay informacion");}
		echo json_encode($json);
		
	}
?>
