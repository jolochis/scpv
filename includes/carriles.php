<?php
error_reporting(E_ALL);
header("Content-type: application/json");
require_once("condb.php");
$jsondata = array();
/*$action  = !empty($_POST["action"])? $_POST["action"] : "";
	switch($action){
		case "obtenerLinea":*/
			$lineaa = $_POST['linea'];
			$qry = ("SELECT tags FROM ways where id='$lineaa'");			
			$rst = pg_query($condb2, $qry) or die(pg_last_error());
			$dq = pg_fetch_array($rst);	
			$tag = $dq['tags'];	
			$str1 = '$array1 = array('; 
			$str2 = $tag;// esta es la cadena que tomas de la base de datos cierto?
			$str3 = ');'; // el eval segun yo nunca debe de llevar y debe de terminar en punto y coma
			$code = $str1.$str2.$str3; // se concatenan las tres cadenas
			eval($code);// se ejecuta como codigo	
				/*if ($array1['oneway']=="no"){
					if ($array1['onWay']){
					$direccion = "Direccion normal"
					}
				else{
					$direccion = "Direccion contraria"
				}
				}
				else{
					$direccion = "Direccion normal"
				}*/
				$conteo = explode(',', $array1['onWay']);
				/*for($j=0;$j<count($conteo);$j++){
					echo $conteo[$j];
					}*/
				$conteo2 = explode(',', $array1['reverseWay']);
				/*for($h=0;$h<count($conteo2);$h++){
					echo $conteo2[$h];
					}*/

				for($i=0;$i<$array1['lanes'];$i++){
					$name="carril$i";
					if ($array1['onWay'] || $array1['reverseWay']){
					for($j=0;$j<count($conteo);$j++){
					if ($conteo[$j]==$i){
						$direccion = "Sentido normal.";
					    $texto="Carril $i, $direccion";
					}
					}
					for($h=0;$h<count($conteo2);$h++){
					if ($conteo2[$h]==$i){
						$direccion = "Sentido Contrario.";
					    $texto="Carril $i, $direccion";
					}
					}
				}
				else{
					$texto="Carril $i";
				}
					$jsondata[]="<li><input type=\"checkbox\" class=\"chekb\" name=\"seleccion\" id=\"$name\" value=\"$direccion\">$texto</li>";
					
					//$jsondata[$i]['check'] = <input type="checkbox" name='$name' value='$name'>'$name';
				}
	/*break;
			
	}*/
	
	echo json_encode($jsondata);

?>
