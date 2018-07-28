<?php
//$condb = pg_connect("host=localhost port=5432 dbname=MapaGuadalajara user=postgres password=user");
//$condb2 = pg_connect("host=localhost port=5432 dbname=MapaGuadalajaraLanes user=postgres password=user");

echo "no fackin actualiza";

$condb = pg_connect("host=127.0.0.1 port=5432  dbname=scpvAllv2 user=postgres password=postgres"); //conectamos a la base de datos
//$condb2 = pg_connect("host=localhost port=5432 dbname=scpv_tags user=postgres password=user"); //conectamos a la 
if(!$condb){
	die("No se pudo conectar a la base de datos: ");
}
/*if(!$condb2){
	die("No se pudo conectar a la base de datos: ".pg_last_error($condb2));
}*/
?>
