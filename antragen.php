<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set( 'display_errors','1');
header("Content-type: application/json");
require_once("includes/condb.php");
require_once("includes/class.Resultados.php");
require_once("includes/class.Lineas.php");
require_once("includes/Utyil/class.Utyil.php");
require_once("includes/Utyil/class.Objects.php");

$simurl="http://83958d7ea85d41df8c8e1ddebc88a4d9.cloudapp.net/WebServiceSimulatorJC.asmx";
$d=$_POST;
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
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		//execute post
		$result = curl_exec($ch);
		//close connection
		curl_close($ch);
		
		return $result;
	}
	
	
	function initUtyl($idp,$pj){
		set_time_limit(0);
		global $condb;
		$consult = "Select * from \"tempp\"({$idp},{$pj})";
		/*$consult2 = "Select * from \"tmatriz\"";*/
		$consult2 = "Select i.\"name\", ST_AsText(St_transform(St_setSRID(coor::geometry,900913),4326)) as lonlat, coor, matriz FROM \"tMatriz\" i";
		//$consult = "Select * from \"generateUtyilData\"({$idp},{$pj})";
		$result = pg_query($condb,$consult);
		$result2 = pg_query($condb,$consult2);
		$xml2;

		if(pg_num_rows($result)>0){
			
			$utyl = new Utyil($_SESSION['currentProp']);
			while($rows = pg_fetch_array($result)){
			
				//echo $rows['_name']." ".$rows['_to']."\n";
				$utyl->addSegment($rows['_name'],$rows['_length'],0,$rows['_lonlat4'],$rows['_lonlat9'],$rows['_segmenttype'], $rows['_father']);
				//$utyl->addSegment($rows['_name'],$rows['_length'],0,$rows['_lonlat4'],$rows['_lonlat9'],trim($rows['_from']),trim($rows['_to']),$rows['_type']);
			}
			if(pg_num_rows($result2)>0){
				$pattern_1 = '/{|}/';
				$replacement_1 = "";
				while($rows = pg_fetch_array($result2)){
					$matriz= preg_replace($pattern_1, $replacement_1, $rows['matriz']);
					$matriz_arr =explode(",", $matriz);
					foreach ($matriz_arr as &$value) {
    					$utyl->addSegment($value,5,0,$rows['lonlat'], $rows['coor'], "intersection", $rows['name']);
					}
				}
			}
			$utyl->createUtyil();
			$xml="<UTYiL>".$utyl->getUtyil()."</UTYiL>";
			$xml2=$xml;						
			$xml2 = str_replace(array("\n","\t","\r"),'',$xml2);
			$xml2 = str_replace('"',"'",$xml2);
			$file ="Simulaciones/utyil{$_SESSION['currentPropUser']}-{$_SESSION['currentProp']}.xml";
			
			file_put_contents($file, $xml2);
		}
		return $xml2;
		
	}
	
	function sendSimulation(){
		GLOBAL $simurl;
		ignore_user_abort(true);
		set_time_limit(0);
		if(isset($_SESSION['currentPropUser'])){
			GLOBAL $condb;
			$dat = $_POST;
			
			if ($xml = initUtyl($_SESSION['currentProp'],$dat['pj'])){
				$xml=preg_replace(array('/\</'),'*',$xml);
				$xml=preg_replace('/\>/','%',$xml);
				// echo $xml;
				// envia archivo
					$url = $simurl.'/ReceiveUtyl';
					
					$fields = array(
						'user' => $_SESSION['currentPropUser'],//566,
						'config' => $_SESSION['currentProp'],//817,
						'TiempoInicial' => $dat['ti'],
						'TiempoFinal' => $dat['tf'],
						'FileUtyl' => $xml
						);
					$res = conectionCurl($fields,$url);				
				$json = array("m"=>"ok","status"=>json_decode($res));
				echo json_encode($json);
			}
		
			else 
				$json = array("m"=>"error");
		}
		else
			$json=array("m"=>"error","status"=>"Sesion no iniciada o caducada");		
	}
	
	
	function getInfo(){
		global $condb;
		$d = $_POST;
		$query = "SELECT * FROM \"getSimulationData\"('{$d['s']}',{$_SESSION['currentProp']},{$d['ti']},{$d['tf']})";
		if($r = pg_query($condb,$query)){
			$row = pg_fetch_array($r);
			
			echo json_encode(array(
				"m"=>"ok",
				"flujo"=>$row[0],
				"distance" => $d['d'],
				"densidad"=>($row[1])/$d['d'])
				
			);
		}
		else{
			echo json_encode(array(
				"m"=>"error",
				"flujo"=>0,
				"densidad"=>0)
			);	
		}
		/*if(($d['tf']-$d['ti'])<=20){
			$densidad = 6;
			$flujo = 3;
		}
		else if(($d['tf']-$d['ti'])>20 && ($d['tf']-$d['ti'])<80){
			$densidad = 4;
			$flujo = 4;
		}
		else{
			$densidad = 4;
			$flujo = 6;
		}*/
		
	}
	
	
	function getFlujo(){
		GLOBAL $simurl;
		if(isset($_SESSION['currentPropUser'])){
			$d = $_POST;
			$url = $simurl.'/Flujo';
			$fields = array(
				'usuario' => $_SESSION['currentPropUser'],//566,
				'configuracion' => $_SESSION['currentProp'],//817,
				'hinicial' => $d['ti'],
				'hfinal' => $d['tf'],
				'segmento' => $d['s']
				);
			
			$json = array("m"=>"ok",
				"status"=> json_decode(conectionCurl($fields,$url)));
		}
		else
			$json=array("m"=>"error","status"=>"Sesion no iniciada o caducada");
		return $json;
		
	}
	
	function getDensidad(){
		GLOBAL $simurl;
		if(isset($_SESSION['currentPropUser'])){
			$d = $_POST;
			$url =$simurl.'/Densidad';
			$fields = array(
				'usuario' => $_SESSION['currentPropUser'],//566,
				'configuracion' => $_SESSION['currentProp'],//817,
				'hinicial' => $d['ti'],
				'hfinal' => $d['tf'],
				'segmento' => $d['s']
				);
			$json = array("m"=>"ok",
			"status"=> json_decode(conectionCurl($fields,$url)));
		}
		else
			$json=array("m"=>"error","status"=>"No se puede consultar la Simulacion");
		return $json;
	}


	function getCuadrante(){
		global $condb;
		$d = $_POST;
		$query = "Select maxx,maxy,minx,miny from propuestas where id = {$d['p']}";
		
		if($result = pg_query($condb,$query)){
			$row = pg_fetch_array($result);
			$json = array(
				"m" => "ok",
				"status" => "Cuadrante obtenido",
				"west" => $row[0],
				"north" => $row[1],
				"east" => $row[2],
				"south" => $row[3]
			);
			startProp($d['p']);
			echo json_encode($json);
		}
		else {
			startProp(null);
			$json=array("m"=>"error","status"=>"No se puede cargar la propuesta");
		}
	}
	
	function compruebaPunto(){
		$lineas = new Lineas();
		
		$point = "POINT({$_POST['x1']} {$_POST['y1']})";
		$line = "{$_POST['srcline']}";
		$valido = $lineas ->comparaPunto($line,$point);
		
		if($_POST['x1']){
			$json = array(
				"valido"=> $valido, 
				"interseccion" => $lineas ->comparaPunto($line,$point)
			);
		}
		else{
			$json = array(
				"valido"=> $valido
			);
		}
		
		echo json_encode($json);
	}
	

	function getLinePoints(){
		GLOBAL $condb;
		$d = $_POST;
		$features;
		$query = "SELECT * FROM \"getLinePoints\"
			({$d['p1']},{$d['p1']},{$d['line']})";
			echo $query;
		$result = pg_query($condb,$query);
		if($result){
			while($row = pg_fetch_array($result)){
				$feature[$row['angle']] = array(
					"type"=>"feature",
					"properties"=>array("angle"=>$row['angle'],
										"graphic"=>'imagenes/direction.png'),
					"geometry"=>(array)json_decode($row['points'])
					);
			}
		}
	}
	
	
	
	/*function sendPrueba(){
		if(isset($_SESSION['userid'])){
			$d = $_POST;
			$file = file_get_contents("Simulaciones/utyil{$d['u']}-{$d['p']}.xml");
			if($file){
				$xml = $file;
				$xml=preg_replace(array('/\</'),'*',$xml);
				$xml=preg_replace('/\>/','%',$xml);
				$url = 'c8ac5fc81d324358a446e5b2d162d247.cloudapp.net/WebServiceSimulatorJC.asmx/ReceiveUtyl';
				$fields = array(
					'user' => 566,//$_SESSION['userid'],
					'config' => 817,//$_SESION['current'],
					'TiempoInicial' => 0,//$d['ti'],
					'TiempoFinal' => 100,//$d['tf'],
					'FileUtyl' => $xml
					);
				$json = array("m"=>"ok",
					"status"=> conectionCurl($fields,$url));
			}
			else 
				$json=array("m"=>"error","status"=>"No Existe Archivo de Simulacion");
		}
		else
			$json=array("m"=>"error","status"=>"Sesion no iniciada o caducada");
		
		echo json_encode($json);
	}*/
	

	function splitFeatures($val,&$feature){
		$prop["id"] = $val['id'];
		$prop["type"] = $val['type'];
		foreach($val['configurations'] as $f2 => $val2){
			$prop["configurations"][]= $val2;
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
		$d=$_POST;
		$objetos = new Objects();
		$aforos = array();
		$semaforos= array();
		$topes= array();

		$feature;
		if($d['p']!=0){
			setPropuesta($d['p'],$d['u']);
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
	
	
	function getSimulationFileUser(){
		$data=$_POST;
		$url="Simulaciones/utyil{$data['u']}-{$data['p']}.xml";
		$_SESSION['currentPropUser'] = $data['u'];
		$_SESSION['currentProp'] = $data['p'];
		if(file_exists($url)){
			$file = file_get_contents($url);
			$simple = json_encode(simplexml_load_string($file));
			echo getFeatures(json_decode($simple,true),$_SESSION['currentProp']);
			//echo json_encode(array("m"=>"ok","status"=> json_decode($simple)));;
		}
		else
			echo json_encode(array("m"=>"error","status"=>"No existe Simulacion"));
	}
	
	
	function getSimulationFile(){
		$data=$_POST;
		$url="Simulaciones/utyil{$_SESSION['currentPropUser']}-{$_SESSION['currentProp']}.xml";
		
		if(file_exists($url)){
			$file = file_get_contents($url);
			$simple = json_encode(simplexml_load_string($file));
			echo getFeatures(json_decode($simple,true),$_SESSION['currentProp']);
			//echo json_encode(array("m"=>"ok","status"=> json_decode($simple)));;
		}
		else
			echo json_encode(array("m"=>"error","status"=>"No existe Simulacion"));
	}
	
	function getFeatures($data,$idp){
		global $condb;
		$geom = array();// contiene la información para dibujar los carriles
		$mainGeom = array();// contiene la información para dibujar la calle principal que contiene los carriles
		$tempCarril = NULL;
		//Extrae las calles principales
		foreach($data['segments'] as $segments){
			//para convertirlo en arreglo cuando sólo hay un elemeto
			if(!isset($segments['segment'][0])){
				$segments['segment'] = array($segments['segment']);
			}
			//extrae cada uno de los carriles internos de la calle
			foreach($segments['segment'] as  $object=>$value){		
				$seg = $value['@attributes'];
				
				if($seg['type']=="segment"){
					$line="LINESTRING({$seg['points']})";
					$consult ="Select ST_AsGeoJSON('{$line}') as geom, \"idCalle\",distance From segments WHERE \"idPropuesta\" = {$idp} AND \"segmentName\" like cast('{$seg['id']}' as text)";
					$result = pg_query($condb,$consult);
					$segment = pg_fetch_array($result);
					//echo ($consult);
					//Obtiene las conexiones del Segmento
					if(key_exists("connection", $value)){
						
						$connection = $value['connection']['c'];
						$nodes = array();
						foreach($connection as $con=>$conName){
							
							if(key_exists("@attributes", $conName)){
								if($conName['@attributes']['type'] == 'NS')
								$nodes[] = $conName['@attributes']['to'];
							}
							else {
								if($conName['type'] == 'NS')
								$nodes[] = $conName['to'];	
							}
						}
					}
					$tempCarril = array(
						"type"=>"Feature",
						"properties" => array("label"=>$seg['id'],"idlinea"=> $segment[1], "name"=>$seg['id'],"density"=>rand(10,100),
							"distance" => ($seg['length']>0)? $seg['length']:2, "connections"=>$nodes),
						"geometry" => (array)json_decode($segment['geom'])
					);
					
					$geom[]=$tempCarril;
				}
			}
//echo var_dump($tempCarril);
//echo var_dump($segments);
			if($tempCarril){
				$mainGeom[] = array(
					"type"=>"Feature",
					"properties" => array("label"=>$segments['@attributes']['name'],"idlinea"=> $tempCarril['properties']['idlinea'], "name"=>$segments['@attributes']['name'],"density"=>rand(10,100),
						"distance" => $tempCarril['properties']['distance']),
					"geometry" => $tempCarril['geometry'],
					"layers" => $geom
				);
			}
			$tempCarril = null;
		}
		//echo var_dump($geom);
		return json_encode(array(
			"type"=>"FeatureCollection",
			"features"=>$mainGeom)
			);
	}
	
	
	function updateSimulation(){
		$data = $_POST;
		$qry="select * from configuracion_simulacion  
			where id_usuario={$data['u']} and id_propuesta={$data['p']}";
		$res = pg_query($condb,$qry);
		if(pg_fetch_array($res)){
			$qry="update configuracion_simulacion set status =1  
				where id_usuario={$data['u']} and id_propuesta={$data['p']}";
			pg_query($condb,$qry);
			echo json_encode(array("m"=>"ok"));
		}
		else
			echo json_encode(array(
				"m"=>"error",
				"status"=>"No existe la configuracion"));
	}
	
	
	
	function estadoSimulaciones(){
		$data=$_POST;
		$result=pg_query($condb,"Select id,id_usuario,url_utyil".
		" from configuracion_sumulacion where status = TRUE");
		while($rows=pg_fetch_array($result)){
			$json[]=$rows;			
		}
		echo json_encode($json);
	}
	
	
	function consultaSimulacion(){
		GLOBAL $condb;
		$dat = $_POST;
		$consult = "Select * from \"getSegmentos\"(
		{$dat['maxx']},{$dat['maxy']},
		{$dat['minx']},{$dat['miny']},
		{$dat['pj']})";
		$result = pg_query($condb,$consult);
	
		if(pg_num_rows($result)>0){
			$utyl = new Utyil();
			while($rows = pg_fetch_array($result)){
				$utyl->addSegment($rows['_name'],$rows['_length'],0,$rows['_lonlat4'],$rows['_lonlat9'],trim($rows['_from']),trim($rows['_to']),$rows['_type'], $rows['_father']);
			}
			
			$utyl->createUtyil();
			$xml="<UTYiL>".$utyl->getUtyil()."</UTYiL>";
			$xml2=$xml;
			$xml=preg_replace(array('/\</'),'*',$xml);
			$xml=preg_replace('/\>/','%',$xml);
			//file_get_contents("http://cad04f4bf5564c938d356203c5ba47ad.cloudapp.net/WebServiceSimulator.asmx?op=RecibirUtyil&user=1&config=1&archivo=\"{$xml}\"");
		
			$xml2 = str_replace(array("\n","\t","\r"),'',$xml2);
			$xml2 = str_replace('"',"'",$xml2);
			$file ="Simulaciones/utyil{$dat['u']}-{$dat['p']}.xml";
			file_put_contents($file, $xml2);
			
			$simple = json_encode(simplexml_load_string($xml2));
			pg_query($condb
			,"SELECT * FROM \"saveSimulation\"({$dat['u']},{$dat['p']},'{$file}')");	
			//echo $simple;
			echo json_encode(array("mensaje"=>"OK"));
		}
	
		else 
			echo json_encode(array("mensaje"=>"Error"));
	}

	
	
	function guardarObjetosv2(){
		$dat = $_POST;
		$json = json_decode($dat['objects'],true);
		foreach($json['object'] as $ob =>$val){
			foreach($val as $data=>$attr){
				//Agregar un método a la funció guardar del objt madre
				$query= "Insert into objetos(\"type\",positionscr)
							values({$attr['type']},{$attr['points']}) 
							returning id";
				$result=$pg_query($condb,$query);
				$id = pg_fetch_array($result);
				
				switch($attr['type']){
					case "semaphore":
						$query="insert into configuracion
						values({$id['id']},{$attr['init']},
						{$attr['end']},{$attr['attr']})
						returning id";
				
						$result=$pg_query($condb,$query);
						$id = pg_fetch_array($result);
						
						$query="insert into estado 
						values({$id['id']},'verde',{$attr['attr1']},0),
						values({$id['id']},'amarillo',{$attr['attr2']},0),
						values({$id['id']},'rojo',{$attr['attr3']},0)";
						
					break;
					case "aforo":
						$query="insert into configuracion
						values({$id['id']},{$attr['init']},
						{$attr['end']},{$attr['attr']})
						returning id";
				
						$result=$pg_query($condb,$query);
					break;
					case "signal":
					break;
					case "bump":
						
					break;
				}
			}
		}
	}

	function guardarObjetos(){
		$dat = $_POST;
		$object =json_decode($dat['objetos'],true);
			
		//foreach($json as $object => $value){
			//foreach($value as $od => $odvalue ){
				
				$geom = $object['type']=="flow"  ?
				"'LINESTRING({$object['points']})'::geometry"
				: "'POINT({$object['points']})'::geometry";
				
				$query = "Insert INTO objetos(type,positionscr,positiondst) VALUES($1,ST_Transform(ST_setSRID({$geom},4326),900913),ST_Transform(ST_setSRID({$geom},4326),900913)) returning id";
				//echo $geom;
				$insert = pg_query_params($condb,$query,array($object['type']));
				$resid = pg_fetch_array($insert);
			//}
		//}
		if ($resid){
			$json = array(
				"mensaje" => "ok",
				"estatus" => "Objeto Guardado"
				);
		}
		else{
			$json = array(
				"mensaje" => "error",
				"estatus" => "Objeto no pudo Guardarse"
			);
		}
		echo json_encode($json);
	}
	
	
	function getAforo(){
		GLOBAL $condb;
		$aforoPoints;
		$feature = array();
		$dat = $_POST;
		/*$query = "Select * FROM \"getAforo\"(
			{$dat['id1']},
			{$dat['id2']},	
			cast('{$dat['line']}' as Text),
			{$dat['pj']});";*/
		$query = "Select * from getpointsfromline(cast('{$dat['line']}' as Text))";
		$rows = pg_query($condb,$query);
		while($result = pg_fetch_array($rows)){
			//$aforoPoints = $result['aforopoints'];
			$feature[] = array(
				"type"=>"feature",
				"properties"=>array("angle"=>$result['angle'],
				//"aforoPoints"=>$aforoPoints,
				"graphic"=>'imagenes/direction.png',"type"=>"aforo",
				"aforo"=>"yes","points"=>""),
				"geometry"=>(array)json_decode($result['punto'])
			);
		}
		
		if(count($feature)<1)
			$json = array("m"=>"error");
		else{
			$json = array(
				"m"=>"ok",
				"features"=>array(
					"type"=>"FeatureCollection",
					"features"=>$feature
					)
			);
		}
		echo json_encode($json);
		
	}
	
	
	function userRegister(){
		GLOBAL $condb;
		$d =$_POST;
		//$d['nemail'] = urldecode($d['nemail']);
		$query = pg_query($condb,
		"SELECT id,benutzername,email 
			from usuario where email 
			like '{$d['nemail']}' or benutzername like '{$d['nuname']}'");
		
		if(pg_num_rows($query) > 0){
			
			$json = array(
				"m" => "error",
				"status" => "Nombre de Usuario o Correo ya existentes");
		}
		else{
			pg_query($condb,"INSERT into usuario 
				(name,benutzername,email,pass)
				values('{$d['nuname']}','{$d['nuname']}','{$d['nemail']}','{$d['npass']}')");
			$json = array(
				"m" => "ok",
				"status"=>"Usuario Ristrado");
		}
		
		echo json_encode($json);
	}

	function userLogin(){
		GLOBAL $condb;
		if(!empty($_POST['uname']) && !empty($_POST['pass']))
		{
			$query = pg_query($condb,
				"SELECT id,name,email 
				from usuario where name 
				like '{$_POST['uname']}' AND pass like '{$_POST['pass']}'");
			if(pg_num_rows($query) > 0){
				$result = pg_fetch_array($query);
				$json = array(
					"m" => "ok",
					"status" => "Usuario Identificado",
					"name"=>$_POST['uname']);
				startUser($result['id'],$_POST['uname']);
			}
			else{
				endLogin();
				$json = array(
					"m" => "error",
					"status" => "Password o correo incorrecto");
			}
		}
		else
		{
			endLogin();
			$json = array(
				"m" => "error",
				"status" => "Password o correo incorrecto");
		}
		
		
		echo json_encode($json);
	}


function guardarPropuesta(){
	GLOBAL $condb;
	$dap = $_POST;
	$fecha = date("Y-m-d H:i:s");
	
	if(isset($_SESSION['userid'])){
		$idu = $_SESSION['userid'];
		$params=array($idu, $fecha, $_POST['oeste'],$_POST['norte'],$_POST['este'], $_POST['sur'] );
		$qry = "INSERT INTO propuestas 
			(iduser,date_hour,maxx,maxy,minx,miny) 
			VALUES ($1,$2,$3,$4,$5,$6) returning id";
		$rst = pg_query_params($condb, $qry, $params) or die(pg_last_error()." ".$qry);
		$row = pg_fetch_array($rst);
		if($row){
			startProp($row['id']);
			initUtyl($row['id'],$_POST['pj']);
			$json=array("m"=>"ok","status"=>"Propuesta Creada");
		}
		else{
			$json=array("m"=>"error","status"=>"No se pudo crear la Propuesta");
		}
	}
	else{
		$json=array("m"=>"error","status"=>"SESION NO INICIADA O VENCIDA");
	}
	echo json_encode($json);
}

function listaSimulaciones(){
	$data=$_POST;
	$result=pg_query($condb,"Select id,id_usuario,url_utyil".
	" from configuracion_sumulacion where status = TRUE");
	while($rows=pg_fetch_array($result)){
		$json[]=$rows;			
	}
	echo json_encode($json);
}


function getPropuesta(){
	GLOBAL $condb;
	$d = $_POST;
	$query="SELECT idprop as propid,iduser,o.id as objid,
		type,ST_AsGeoJSON(ST_asText(positionscr))
		FROM objetos o join
		propuestas p on p.id = idprop join
		usuario u on u.id = iduser";
		
	$result = pg_query($condb,$query);
	if($result){
		while($row = pg_fetch_array($result)){
			
		}
	}
}


function listaPropuestas(){
	GLOBAL $condb;
	if(isset($_SESSION['userid']))
		$idu = $_SESSION['userid'];
	else{
		$idu=0;
		
	}
	$data=$_POST;
	$query;
	$list;
	if($data['other'] == 1){
		$query="Select minx,miny,maxx,maxy,p.id,iduser,nombre,name,date_hour
		from propuestas p
		join usuario u on iduser = u.id
		WHERE iduser != {$idu} and pass notnull" ;
	}
	else {
		$query="Select minx,miny,maxx,maxy,p.id,iduser,nombre,name,date_hour
		from propuestas p
		join usuario u on iduser = u.id
		WHERE iduser = {$idu} and pass notnull";
	}
	$result=pg_query($condb,$query);
	
	if($result){
		while($rows=pg_fetch_array($result)){
			$list[]=array(
				"minx"=>$rows['minx'],"miny"=>$rows['miny'],
				"maxy"=>$rows['maxy'],"maxx"=>$rows['maxx'],
				"id"=>$rows['id'],"iduser"=>$rows['iduser'],
				"pname"=>$rows['nombre'],
				"uname"=>$rows['name'],"fecha"=>$rows['date_hour']);
		}
		if(isset($list))
			$json=array("m"=>"ok","status"=>$list);
		else
			$json=array("m"=>"erro","status"=>"No Hay Propuestas");
	}
	else{
		$json=array("m"=>"error","status"=>"No hay Propuestas");
	}
	echo json_encode($json);

}


function eliminarObjeto(){
	GLOBAL $condb;
	$d = $_POST;
	pg_query($condb,"DELETE FROM configuracion 
		WHERE idobject = {$d['o']}");
	pg_query($condb,"DELETE FROM objetos 
		WHERE id = {$d['o']}");
}

function actualizaObjeto(){
	GLOBAL $condb;
	$datos = $_POST;
	$idu = 1;//$row['id'];
	$pdst = 0;
	$ldst = 0;
	if(isset($_SESSION['userid'])){
		$params2=array($idu2,$datos["configuracionInicial"]["tiempo_inicial"],$datos["configuracionInicial"]["tiempo_final"],$datos["configuracionInicial"]["estado"]);
		$qry2 = "UPDATE configuracion SET 
		init=$2,tend=$3,attr=$4 where idobject=$1";
		pg_query_params($condb, $qry2, $params2) or die(pg_last_error());
		
		/*for ($i=0;$i<$datos["checked"];$i++){
			pg_query_params($condb,"INSERT INTO calles (idobj,nolinescr,nolinedst,dir,ncarril) VALUES($1,$2,$3,$4,$5);",array($idu2,$datos["objeto"]["lineascr"],$datos["objeto"]["lineadst"],$datos["direccion"][$i],$datos["carril"][$i]) ) or die("Error: ".pg_last_error($condb));
				
		}*/
		
		if($datos["type"] == "semaforo"){
		//ahora insertamos las configuraciones secundarias.
			if($datos["configuracionInicial"]["estado"] == "rojo"){
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][2]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][1]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
			if($datos["configuracionInicial"]["estado"] == "amarillo"){
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][0]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
			if($datos["configuracionInicial"]["estado"] == "verde"){
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][2]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"UPDATE estado SET attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']} and attr1 like $1;",array($datos["configuraciones"][0]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
				
		}
		
		if($datos["type"] == "aforo"){
			foreach($datos["configuraciones"] as $c){
				pg_query_params($condb,"update estado SET attr1=$1,attr2=$2,attr3=$3 WHERE idconfig = {$datos['c']};",array($idu3,$datos["objeto"]["lineascr"],$c["tiempo_inicial"],$c["tiempo_final"]) ) or die("Error: ".pg_last_error($condb));
				break;
			}
		}
		
		//print_r($_POST);
		unset($datos);
	}
	else{
		$json=array("m"=>"No ha propuesta creada");
	}
	echo json_encode($json);
}


function guardarObjeto(){
	GLOBAL $condb;
	$datos = $_POST;
	$idu = 1;//$row['id'];
	$pdst = 0;
	$ldst = 0;
	if(isset($_SESSION['propid'])){
		$params=array($datos["objeto"]["tipo"],$_SESSION['propid'],
			$datos["objeto"]["posiciones"],
			$datos["objeto"]["lineascr"],
			$datos["objeto"]["posiciones"],
			$datos["objeto"]["lineadst"]);
		$qry = "INSERT INTO objetos (type,idprop,positionscr,linescr,positiondst,linedst) 
			VALUES ($1,$2,ST_GeomFromText($3,900913),$4,ST_GeomFromText($5,900913),$6) returning id";
			$rst_ido = pg_query_params($condb, $qry, $params) or die(pg_last_error());
			$ido =  pg_fetch_array($rst_ido);
			//$qry2 = ("SELECT id FROM objetos ORDER BY id DESC LIMIT 1");
			//$rst2 = pg_query($condb, $qry2) or die(pg_last_error());
			//$row2 = pg_fetch_array($rst2);
			$idu2 = $ido['id'];//$row2['id'];
			
		if($datos["objeto"]["tipo"] == "semaforo" OR $datos["objeto"]["tipo"] == "desnivel" OR $datos["objeto"]["tipo"] == "aforo" OR $datos["objeto"]["tipo"] == "tope" OR $datos["objeto"]["tipo"] == "pauto"){
			//ahora si insertamos la configuracuion inicial.
			$params2=array($idu2,$datos["configuracionInicial"]["tiempo_inicial"],$datos["configuracionInicial"]["tiempo_final"],$datos["configuracionInicial"]["estado"]);
			$qry2 = "INSERT INTO configuracion (idobject,init,tend,attr) VALUES($1,$2,$3,$4)";
			pg_query_params($condb, $qry2, $params2) or die(pg_last_error());
			
			/*for ($i=0;$i<$datos["checked"];$i++){
				pg_query_params($condb,"INSERT INTO calles (idobj,nolinescr,nolinedst,dir,ncarril) VALUES($1,$2,$3,$4,$5);",array($idu2,$datos["objeto"]["lineascr"],$datos["objeto"]["lineadst"],$datos["direccion"][$i],$datos["carril"][$i]) ) or die("Error: ".pg_last_error($condb));
					
				}*/
		}
		else{
			//echo var_dump($datos["configuraciones"]);
		foreach($datos["configuraciones"] as $c){
			$params2=array($idu2,$c["configuracionInicial"]["tiempo_inicial"],$c["configuracionInicial"]["tiempo_final"], 1);
			$qry2 = "INSERT INTO configuracion (idobject,init,end,attr) VALUES($1,$2,$3,$4)";
			pg_query_params($condb, $qry2, $params2) or die(pg_last_error());
			}
		}
		$qry3 = ("SELECT id FROM configuracion ORDER BY id DESC LIMIT 1");
		$rst3 = pg_query($condb, $qry3) or die(pg_last_error());
		$row3 = pg_fetch_array($rst3);
		$idu3 = $row3['id'];
		if($datos["objeto"]["tipo"] == "semaforo"){
			
		//ahora insertamos las configuraciones secundarias.
			if($datos["configuracionInicial"]["estado"] == "red"){
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
			if($datos["configuracionInicial"]["estado"] == "yellow"){
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
			if($datos["configuracionInicial"]["estado"] == "green"){
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
				
		}
		if($datos["objeto"]["tipo"] == "desnivel"){
			pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][1]["estado"],0) ) or die("Error: ".pg_last_error($condb)); //TIPO DE DESNIVEL
			//pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],0,0, 0, $idobjeto ) ) or die("Error: ".pg_last_error($condb)); //NUMERO DE CARRILES
		}
		if($datos["objeto"]["tipo"] == "aforo"){
			foreach($datos["configuraciones"] as $c){
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["objeto"]["lineascr"],$c["tiempo_inicial"],$c["tiempo_final"]) ) or die("Error: ".pg_last_error($condb));
				break;
			}
		}
		
		//print_r($_POST);
		unset($datos);
		$json=array("m"=>"ok","ido"=>$idu2);
	}
	else{
		$json=array("m"=>"Error","status"=>"propuesta no creada");
	}
	echo json_encode($json);
}


function startUser($id,$name){
	$_SESSION['userid']=$id;
	$_SESSION['currentPropUser'] = $id;
	$_SESSION['name']=$name;
}

function setPropuesta($idp,$idu){
	$_SESSION['currentProp'] = $idp;
	$_SESSION['currentPropUser'] = $idu;
}

function startProp($id){
	$_SESSION['propid']=$id;
	$_SESSION['currentProp'] = $id;
}

function getUser(){
	if(isset($_SESSION['userid']))
		$json=array("m"=>"ok","n"=>$_SESSION['name']);
	else
		$json=array("m"=>"error");
		
	echo json_encode($json);
}

function endLogin(){
	session_destroy();
}

?>
