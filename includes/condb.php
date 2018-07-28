<?php

//$condb = pg_conne	ct("host=localhost dbname=gdlMap_ user=postgres password=mapas") or die("Error en la conexion");

//$condb2 = pg_connect("host=localhost port=5432 dbname=scpv_tags user=postgres password=user"); //conectamos a la
$condb = pg_connect("host=localhost port=5432 dbname=scpv user=postgres password=postgres"); //conectamos a la 
if(!$condb){
	die("Error de conexion DB: ".pg_last_error($condb));
}
/*if(!$condb2){
	die("No se pudo conectar a la base de datos: ".pg_last_error($condb2));
}*/
?>
