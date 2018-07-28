<?php
error_reporting(E_ALL); 
ini_set( 'display_errors','1');
header("Content-type: application/json");
require_once("includes/condb.php");
require_once("includes/class.Resultados.php");
require_once("includes/class.Lineas.php");
require_once("includes/Utyil/class.Utyil.php");
$action  = !empty($_GET["action"])? $_GET["action"] : "";

switch($action){

	case "updateSimulation":
		$data = $_GET;
		$qry="update configuracion_simulacion set status=1 
		where id_usuario={$data['u']} and id={$data['p']}";
		$res = pg_query($condb,$qry);
		if($res)
			echo json_encode(array("mensaje"=>"OK"));
		else
			echo json_encode(array("mensaje"=>"ERROR"));
	break;
	
	case "sica":
		$file =file_get_contents("http://768dc3aa53bb40ecb0d83c9c700e183a.cloudapp.net/WebServiceCreateTicketSICA.asmx/CreateTicket_SICA?Name=IsaiasAugusto&Department=office&Comments=green");
		echo var_dump($file);
	break;

	case "consultaSimulador":
		$data=$_GET;
		$file = file_get_contents("Simulaciones/utyil{$data['u']}-{$data['p']}.xml");
		$simple = json_encode(simplexml_load_string($file));
		echo $simple;
	break;

	case "consultaSimulacion":
		//echo("OK");
		$dat = $_GET;
		$consult = "Select * from \"getSegmentos\"(
		{$dat['maxx']},{$dat['maxy']},{$dat['minx']},{$dat['miny']},
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
			file_put_contents("Simulaciones/utyil.xml", $xml);
			file_put_contents("Simulaciones/utyil{$dat['u']}-{$dat['p']}.xml", $xml2);
			$simple = json_encode(simplexml_load_string($xml2));
			pg_query($condb,"update configuracion_simulacion set status=FALSE
			WHERE id_usuario =1");
			echo $simple;
		}
	
		else 
			echo "Uups, hayun error";
	
	break;

	//case "1":
	case "guardarObjetos":
			$dat = $_GET;
			$json =json_decode($dat['objeto'],true);
			//echo var_dump($json);
			foreach($json['objetos'] as $object => $value){
				foreach($value as $od => $odvalue ){
				
					$geom = sizeof(explode(' ',$odvalue['coordenadas'])) > 2  ?
					"'LINESTRING({$odvalue['coordenadas']})'::geometry"
					: "'POINT({$odvalue['coordenadas']})'::geometry";
					$query = "Insert INTO objetos(type,positionscr) VALUES($1,{$geom}) returning id";
					//echo $geom;
					$insert = pg_query_params($condb,$query,array($odvalue['tipo']));
					$resid = pg_fetch_array($insert);
					if($resid[0]){
					
					}
				}
			}		
			//pg_free_result($insert);
	break;
}

?>
