<?php

//local$condb = pg_connect("host=localhost port=5432 dbname=scpv user=postgres password=postgres"); //conectamos a la 
$condb = pg_connect("host=ec2-50-16-195-131.compute-1.amazonaws.com port=5432 dbname=d7li5r8db6avt user=jfeebsjanpnbxo password=87aa5eea182240a4804e31162a7702fad71127cdd0ca0f72d1399460a430c340") or die("Error en la conexion"); //heroku
if(!$condb){
	die("Error de conexion DB: ".pg_last_error($condb));
}
/*if(!$condb2){
	die("No se pudo conectar a la base de datos: ".pg_last_error($condb2));
}*/
?>
