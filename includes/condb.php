<?php

//local$condb = pg_connect("host=localhost port=5432 dbname=scpv user=postgres password=postgres"); //conectamos a la 
$condb = pg_connect("host=ec2-54-204-23-228.compute-1.amazonaws.com port=5432 dbname=ds6ceq7df0qqm user=ruzvacvovwxefq password=6c17463c8074344e6d02cb7770477bcf658c3c78dd8c3bcaee419c24ccdd7666") or die("Error en la conexion"); //heroku
if(!$condb){
	die("Error de conexion DB: ".pg_last_error($condb));
}
/*if(!$condb2){
	die("No se pudo conectar a la base de datos: ".pg_last_error($condb2));
}*/
?>
