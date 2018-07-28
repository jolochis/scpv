<?php
session_start();
error_reporting(E_ALL);
ini_set( 'display_errors','1');
header("Content-type: application/json");
require_once("includes/condb.php");
require_once("includes/class.Resultados.php");
require_once("includes/class.Lineas.php");
require_once("includes/Utyil/class.Utyil.php");

$action  = !empty($_POST["action"])? $_POST["action"] : "";
$json = array();
$json = array("mensaje" => $_POST["action"]);

switch($action){
	
	case "getFeatures": 
		$d=$_POST;
		$geom = array();

		//file_put_contents("errores.txt", var_dump($d["features"]),FILE_APPEND);
		//file_put_contents("errores.txt", var_dump($d.features.segment),FILE_APPEND);
		
		
		foreach($d["features"]["segment"] as  $object=>$value)
		{
			
			if($value['@attributes']['type']=="segment"){
				$seg = $value['@attributes'];
				$line="LINESTRING({$seg['points']})";
				$consult ="Select ST_AsGeoJSON('{$line}') as geom";
				$result = pg_query($condb,$consult);
				$segment = pg_fetch_array($result);
				$geom[]=array(
						"type"=>"Feature",
						"properties" => array("name"=>$seg['id'],"distance"=>$seg['length']),
						"geometry" => (array)json_decode($segment['geom'])
					);
			}
		}
		//echo var_dump($geom);
		echo json_encode(array(
			"type"=>"FeatureCollection",
			"features"=>$geom)
			);
		
	break;

	case "updateSimulation":
		$data = $_POST;
		$qry="update configuracion_simulacion set status=1 
		where id_usuario={$data['u']} and id={$data['p']}";
		$res = pg_query($condb,$qry);
		if($res)
			echo json_encode(array("m"=>"ok"));
		else
			echo json_encode(array("m"=>"error"));
	break;
	
	case "consultaSegmento":
		$file =file_get_contents("http://?c=1&p=1&u=1&s={$_POST['segmento']}");
		echo json_encode($file);
	break;

	case "getSimulacionFile":
		$data=$_POST;
		$file = file_get_contents("Simulaciones/utyil{$data['u']}-{$data['p']}.xml");
		if($file){
			$simple = json_encode(simplexml_load_string($file));
			echo $simple;
		}
		else
			echo json_encode(array("mensaje"=>"Error"));
	break;
	
	case "estadoSimulaciones":
		$data=$_POST;
		$result=pg_query($condb,"Select id,id_usuario,url_utyil".
		" from configuracion_sumulacion where status = TRUE");
		while($rows=pg_fetch_array($result)){
			$json[]=$rows;			
		}
		echo json_encode($json);
		
	break;

	case "consultaSimulacion":
	
		$dat = $_POST;
		$consult = "Select * from \"getSegmentos\"(
		{$dat['maxx']},{$dat['maxy']},
		{$dat['minx']},{$dat['miny']},
		{$dat['pj']})";
		$result = pg_query($condb,$consult);
	
		if(pg_num_rows($result)>0){
			$utyl = new Utyil();
			while($rows = pg_fetch_array($result)){
				$utyl->addSegment($rows['_name'],$rows['_length'],0,$rows['_lonlat4'],$rows['_lonlat9'],trim($rows['_from']),trim($rows['_to']),$rows['_type']);
			}
			
			$utyl->createUtyil();
			$xml="<UTYiL>".$utyl->getUtyil()."</UTYiL>";
			$xml2=$xml;
			$xml=preg_replace(array('/\</'),'*',$xml);
			$xml=preg_replace('/\>/','%',$xml);
			//$file = file_get_contents("http://6daf9ad9ac574635894838095d1f4ae7.cloudapp.net/WS_Ciudadela.asmx/RecibirUtyil?user=1&config=1&archivo=\"{$xml}\"");
		
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
	
	break;

	case "guardarObjetosv2":
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
					case "signal":break;
					case "bump":break;
				}
			}
		}
	break;


	case "guardarObjetos":
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
		
	break;

	case "userRegister":
		$d =$_POST;
		//$d['nemail'] = urldecode($d['nemail']);
		$query = pg_query($condb,
		"SELECT id,name,email 
			from usuario where email 
			like '{$d['nemail']}' or name like '{$d['nuname']}'");
		
		if(pg_num_rows($query) > 0){
			
			$json = array(
				"m" => "error",
				"status" => "Nombre de Usuario o Correo ya existentes");
		}
		else{
			pg_query($condb,"INSERT into usuario 
				(name,email,pass)
				values('{$d['nuname']}','{$d['nemail']}','{$d['npass']}')");
			$json = array(
				"m" => "ok",
				"status"=>"Usuario Ristrado");
		}
		
		echo json_encode($json);
	break;

	case "userLogin":
		startUser(4);
		$query = pg_query($condb,
			"SELECT id,name,email 
			from usuario where name 
			like '{$_POST['uname']}' AND pass like '{$_POST['pass']}'");
		if(pg_num_rows($query) > 0){
			$result = pg_fetch_array($query);
			$json = array(
				"m" => "ok",
				"status" => "Usuario Identificado",
				"id" =>  $result['id'],
				"name" => $result['name']);
			startUser($result['id']);
		}
		else{
			$json = array(
				"m" => "error",
				"status" => "Password o correo incorrecto");
		}
		
		echo json_encode($json);
		
	break;

	case "consultaCalle":
		//$point = "POINT({$_POST['x']} {$_POST{['y']})";

		/*$rows = pg_query($condb,"Select calle from 
		(SELECT asText(way) as calle from planet_osm_line) as t
		where ST_Contains(ST_Buffer(calle,4),'{$point}'::geometry)");
		if(pg_num_rows($rows)>0)
		{
			$result = pg_fetch_array($rows);
			$json = array(
				"mensaje" => "ok",
				"calle" => $result['name'],
				"coordenadas" => $result['calle']
			); 
		}
		else
		{*/
			$json = array("mensaje" => "Calle Encontrada");
		//}
		//pg_free_result($rows);
		echo json_encode($json);
	break;
	
	case "consultaObjetos":
		//Versión Dummy
		$aforos = array();
		$semaforos = array();
		$pattern = array('/[A-Z]/','/\(/','/\)/');
		$box=$_POST;
		
		$consult ="Select asText(ST_Transform(St_intersection(
		ST_MakeEnvelope({$box['maxx']},{$box['maxy']},{$box['minx']},{$box['miny']},900913),st_setSRID(positionscr,900913)),4326)) as pos
		FROM objetos
		WHERE st_setSRID(positionscr,900913) && st_setSRID('BOX3D({$box['maxx']} {$box['maxy']},{$box['minx']} {$box['miny']})'::box3d,900913) and type = 'semaforo'";
		
		$rows = pg_query($condb,$consult);
		$semaforo = pg_fetch_array($rows);
		if(pg_num_rows($rows)>0){
			do{
				$semaforos[]=
					array("tipo" => "semaforo",
						"periodo inicio"=>"0",
						"periodo fin"=>"3600",
						"unidad"=>"s",
						"coordenadas" => preg_replace($pattern,'',$semaforo["pos"]),
						"rojo"=>"30",
						"amarillo"=>"20",
						"verde"=>"25",
						"inicio"=>"rojo"
					);
					
			}while(($semaforo = pg_fetch_array($rows)));
			//$objetos[]=$semaforos;
		}
		pg_free_result($rows);
		
		/*
		$consult ="Select asText(ST_Transform(St_intersection(
		ST_MakeEnvelope({$box['maxx']},{$box['maxy']},{$box['minx']},{$box['miny']},900913),st_setSRID(positionscr,900913)),4326)) as pos
		FROM objetos
		WHERE st_setSRID(positionscr,900913) && st_setSRID('BOX3D({$box['maxx']} {$box['maxy']},{$box['minx']} {$box['miny']})'::box3d,900913) and type = 'aforo'";
		
		$rows = pg_query($condb,$consult);
		$aforo = pg_fetch_array($rows);
		if(pg_num_rows($rows)>0){
			do{
				$aforos[]=
					array("tipo" => "aforo",
						"periodo inicio" => "0",
						"periodo fin" => "3600",
						"coordenadas" => preg_replace($pattern,'',$aforo["pos"]),
						"flujo" => "100",
						"unidad" => "s"
					)
					;
					
			}while(($aforo = pg_fetch_array($rows)));
			$objetos[]=$aforos;
		}
		pg_free_result($rows);
		*/
		
		if($semaforos)
			$json = array("mensaje" => "ok",
				"objetos" =>array("semaforos"=>$semaforos)
			);
		else
			$json = array("mensaje" => "Error",
				"objetos" => "No hay Objetos  en {$box['maxx']},{$box['maxy']},{$box['minx']},{$box['miny']}"
			);
		echo json_encode($json);
				
	break;

	/*
	 * Valida el aforo y devuelve cada algunos punto que conforman 
	 * la geometria del aforo
	 * */
	case "getAforo":
		$feature = array();
		$dat = $_POST;
		$query = "Select * FROM \"getAforo\"(
			{$dat['id1']},
			{$dat['id2']},
			cast('{$dat['line']}' as Text),
			{$dat['pj']});";
		$rows = pg_query($condb,$query);
		while($result = pg_fetch_array($rows)){
			$feature[] = array(
				"type"=>"feature",
				"properties"=>array("angle"=>$result['angle'],
									"graphic"=>'imagenes/direction.png'),
				"geometry"=>(array)json_decode($result['points'])
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
		
	break;

	case "getSubLinea":
		$lineas = new Lineas();
		if(isset($_POST["point1"]))
			$subLinea = $lineas->getPuntosSubLinea($_POST["line"],$_POST["point1"],$_POST["point2"]);
		else
			$subLinea = $lineas->getPuntosLinea($_POST["line"]);
		$json = array(
			"features" => $subLinea
		);
		echo json_encode($json);
	
	break;

	case "compruebaPunto":
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
	break;

	case "compruebaLinea":
		$lineas = new Lineas();
		
		$linea1 = "LINESTRING({$_POST['x1']} {$_POST['y1']},{$_POST['x2']} {$_POST['y2']})";
		$linea2 = $_POST['linea'];
		$simil = $lineas->comparaLineas($linea1,$linea2);
		$json = array(
			"igual"=> $simil["igual"]
		);
		//$json = $simil;
		//echo $json;
		echo json_encode($json);
	break;
	
	case "getSeleccion":
		$lineas = new Lineas();
		$lineas->consultaSeleccion($_POST["idosm"]);

		$json= array(
			"features" => $lineas->getSeleccion(),
			"otro" => $_POST["idosm"]
		);
		
		echo json_encode($json);
	break;
	
	case "getInterseccionPuntos":
		
		$lineas = new Lineas();
		$lineas->setNorte(empty($_POST["norte"]) ? "" : (int)$_POST["norte"]);
		$lineas->setSur(empty($_POST["sur"]) ? "" : (int)$_POST["sur"]);
		$lineas->setEste(empty($_POST["este"]) ? "" : (int)$_POST["este"]);
		$lineas->setOeste(empty($_POST["oeste"]) ? "" : (int)$_POST["oeste"]);
		
		$lineas->setInterId(empty($_POST["idosm"]) ? "" : $_POST["idosm"]);
		$lineas->consultaSeleccion($_POST["idosm"]);
		
		if(!$lineas->consultaIntersecciones())
		{
			$json1= array(
				"hayError" => false,
				"features" => $lineas->getInterLineas()
			);
			
			$json2 = array(
				"hayError" => false,
				"features" => $lineas->getInterPuntos()
			);
			
			$json3 = array(
				"features" => $lineas->getSeleccion(),
				"otro" => $_POST["idosm"]
			);
			
			$json = array(
			"lineas" =>(array) $json1,
			"puntos" =>(array) $json2,
			"seleccion" =>(array) $json3
			);
		}
		
		echo json_encode($json);
	break;
	
	
	case "getResultadosSimulador":
		$resultados = new Resultados();		
		$resultados->setInicio(empty($_POST["inicio"])?0:(int)$_POST["inicio"]);
		$resultados->setNumero(empty($_POST["numero"])?-1:(int)$_POST["numero"]);
		if(!$resultados->leerArchivo()){
			$json["errorMsj"] = $resultados->getError();
		}
		else{
			$json = array(
				"resultados" => $resultados->getResultados(),
				"errorMsj" => ""
				//"interpolaciones" => $resultados->getInterpolaciones() 
			);
		}
		echo json_encode($json);
	break;
	
	case "getSegmentosEnCaja":
			
		$lineas = new Lineas();
		$lineas->setNorte(empty($_POST["norte"]) ? "" : (int)$_POST["norte"]);
		$lineas->setSur(empty($_POST["sur"]) ? "" : (int)$_POST["sur"]);
		$lineas->setEste(empty($_POST["este"]) ? "" : (int)$_POST["este"]);
		$lineas->setOeste(empty($_POST["oeste"]) ? "" : (int)$_POST["oeste"]);
		if($lineas->consultarLineas()){
			$json = array(
				"hayError" => false,
				"errorMsj" => "",
				"features" => $lineas->getLineas()
			);
		}
		else{
			$json = array(
				"hayError" => true,
				"errorMsj" => $lineas->getError()
			);
		}
		
		
		echo json_encode($json);
	break;
	
	case "guardarUsuario":
			$dat = $_POST;
			if(isset($_POST['name'])){
				$inob = pg_query_params($condb,"INSERT INTO usuario (name,email) VALUES($1,$2) RETURNING id ;",array($dat["name"],$dat["email"])) or die("Error: ".pg_last_error($condb));
				$json = array(
				"mensaje" => "ok",
				"estatus" => "Usuario Registrado",
				"name" => $dat['name']);
				echo json_encode($json);
			}
			else
				$inob = pg_query_params($condb,"INSERT INTO usuario (name,email) VALUES($1,$2) RETURNING id ;",array($dat["p_user"]["name"],$dat["p_user"]["email"])) or die("Error: ".pg_last_error($condb));
	break;
	
	case "guardarPropuesta":
			//$qry = ("SELECT id FROM p_user ORDER BY id DESC LIMIT 1");
			//echo "bu";
			//$polig = "POLYGON((".$this->oeste." ".$this->norte.",".$this->oeste." ".$this->sur.",".$this->este." ".$this->sur.",".$this->este." ".$this->norte.",".$this->oeste." ".$this->norte."))";
			$dap = $_POST;
			//$idu = pg_query_params("SELECT id FROM p_user ORDER BY id DESC LIMIT 1", $condb);
			//echo $idu;
			//$fecha = date("Y-m-d");
			//$fecha = date("d-m-Y H:i:s");
			$fecha = date("Y-m-d H:i:s");
			$qry = ("SELECT id FROM usuario ORDER BY id DESC LIMIT 1");
			$rst = pg_query($condb, $qry) or die(pg_last_error()." ".$query);
			$row = pg_fetch_array($rst);
			$idu = $row['id'];
			$params=array($idu, $fecha, $_POST['sur'], $_POST['este'], $_POST['oeste'], $_POST['norte']);
			$qry = "INSERT INTO propuestas (iduser,date_hour,minx,maxy,miny,maxx) VALUES ($1,$2,$3,$4,$5,$6)";
			pg_query_params($condb, $qry, $params) or die(pg_last_error()." ".$qry);
			//$idu = pg_query_params("SELECT id FROM p_user ORDER BY id DESC LIMIT 1", $condb);
			//$dp = pg_fetch_array($idu);
			//$iduser = $dp["id"];
			//echo $idu;
			//pg_free_result($idu);
			//$inob = pg_query_params($condb,"INSERT INTO p_proposals (iduser,date_hour,norte,sur,este,oeste) VALUES($6,$5,$1,$2,$3,$4)RETURNING id ;",array($iduser,$fech,$dap["p_proposal"]["norte"],$dap["p_proposal"]["sur"],$dap["p_proposal"]["este"],$dap["p_proposal"]["oeste"])) or die("Error: ".pg_last_error($condb));
			//$inob = pg_query_params($condb,"INSERT INTO p_proposals (iduser,date_hour,norte,sur,este,oeste) VALUES($iduser,$fecha,$1,$2,$3,$4)RETURNING id ;",array($iduser,$fech,$dap["p_proposal"]["norte"],$dap["p_proposal"]["sur"],$dap["p_proposal"]["este"],$dap["p_proposal"]["oeste"])) or die("Error: ".pg_last_error($condb));
			//$inob = pg_query_params($condb,"INSERT INTO p_proposals (iduser,date_hour,norte,sur,este,oeste) VALUES($iduser,$fecha,$1,$2,$3,$4)") or die("Error: ".pg_last_error($condb));
		break;
		
		
	case "guardarObjeto":
		$datos = $_POST;
		$idu = 1;//$row['id'];
		$pdst = 0;
		$ldst = 0;
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
				
				for ($i=0;$i<$datos["checked"];$i++){
					
					pg_query_params($condb,"INSERT INTO calles (idobj,nolinescr,nolinedst,dir,ncarril) VALUES($1,$2,$3,$4,$5);",array($idu2,$datos["objeto"]["lineascr"],$datos["objeto"]["lineadst"],$datos["direccion"][$i],$datos["carril"][$i]) ) or die("Error: ".pg_last_error($condb));
					
				}
			}
			else{
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

			if($datos["configuracionInicial"]["estado"] == "rojo"){
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
			if($datos["configuracionInicial"]["estado"] == "amarillo"){
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
			}
			if($datos["configuracionInicial"]["estado"] == "verde"){
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
	break;
	/*case "guardarObjeto":
		$datos = $_POST;
		$qry = ("SELECT id FROM propuestas ORDER BY id DESC LIMIT 1");
			$rst = pg_query($condb, $qry) or die(pg_last_error()." ".$qry);
			$row = pg_fetch_array($rst);
			$idu = $row['id'];
			$pdst = 0;
			$ldst = 0;
			$params=array($datos["objeto"]["tipo"],$idu,$datos["objeto"]["posiciones"],$datos["objeto"]["lineascr"],$datos["objeto"]["posiciones"],$datos["objeto"]["lineadst"]);
			$qry = "INSERT INTO objetos (type,idprop,positionscr,linescr,positiondst,linedst) VALUES ($1,$2,ST_GeomFromText($3,900913),$4,ST_GeomFromText($5,900913),$6)";
			pg_query_params($condb, $qry, $params) or die(pg_last_error()." ".$qry." {$datos["objeto"]["posiciones"]}");
		//$inob = pg_query_params($condb,"INSERT INTO p_user (name,email) VALUES($1,$2) RETURNING id ;",array($datos["p_user"]["name"],$datos["p_user"]["email"])) or die("Error: ".pg_last_error($condb));
			$qry2 = ("SELECT id FROM objetos ORDER BY id DESC LIMIT 1");
			$rst2 = pg_query($condb, $qry2) or die(pg_last_error()." ".$qry2);
			$row2 = pg_fetch_array($rst2);
			$idu2 = $row2['id'];
			
			if($datos["objeto"]["tipo"] == "semaforo" OR $datos["objeto"]["tipo"] == "desnivel" OR $datos["objeto"]["tipo"] == "aforo" OR $datos["objeto"]["tipo"] == "tope" OR $datos["objeto"]["tipo"] == "pauto"){
				//ahora si insertamos la configuracuion inicial.
				$params2=array($idu2,$datos["configuracionInicial"]["tiempo_inicial"],$datos["configuracionInicial"]["tiempo_final"],$datos["configuracionInicial"]["estado"]);
				$qry2 = "INSERT INTO configuracion (idobject,init,tend,attr) VALUES($1,$2,$3,$4)";
				pg_query_params($condb, $qry2, $params2) or die(pg_last_error()." ".$qry2);
				
				for ($i=0;$i<$datos["checked"];$i++){
					pg_query_params($condb,"INSERT INTO calles (idobj,nolinescr,nolinedst,dir,ncarril) VALUES($1,$2,$3,$4,$5);",array($idu2,$datos["objeto"]["lineascr"],$datos["objeto"]["lineadst"],$datos["direccion"][$i],$datos["carril"][$i]) ) or die("Error: ".pg_last_error($condb));
				}
			}
			else{
				foreach($datos["configuraciones"] as $c){
					$params2=array($idu2,$c["configuracionInicial"]["tiempo_inicial"],$c["configuracionInicial"]["tiempo_final"], 1);
					$qry2 = "INSERT INTO configuracion (idobject,init,end,attr) VALUES($1,$2,$3,$4)";
					pg_query_params($condb, $qry2, $params2) or die(pg_last_error()." ".$qry2);
				}
			}
				$qry3 = ("SELECT id FROM configuracion ORDER BY id DESC LIMIT 1");
				$rst3 = pg_query($condb, $qry3) or die(pg_last_error()." ".$qry3);
				$row3 = pg_fetch_array($rst3);
				$idu3 = $row3['id'];
			if($datos["objeto"]["tipo"] == "semaforo"){
				//ahora insertamos las configuraciones secundarias.

				if($datos["configuracionInicial"]["estado"] == "rojo"){
					pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][0]["estado"],$datos["configuraciones"][0]["tiempo_final"],$datos["configuraciones"][0]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
					pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][2]["estado"],$datos["configuraciones"][2]["tiempo_final"],$datos["configuraciones"][2]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
					pg_query_params($condb,"INSERT INTO estado (idconfig,attr1,attr2,attr3) VALUES($1,$2,$3,$4);",array($idu3,$datos["configuraciones"][1]["estado"],$datos["configuraciones"][1]["tiempo_final"],$datos["configuraciones"][1]["tiempo_inicial"]) ) or die("Error: ".pg_last_error($condb));
				}
			}
			
		break;		*/
}

function startUser($id){
	$_SESSION['userid']=$id;
}

function startProp($id){
	$_SESSION['propid']=$id;
}
?>

