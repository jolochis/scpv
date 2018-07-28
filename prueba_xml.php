<?php
require_once("includes/condb.php");

$a = simplexml_load_file('simulador/ejemplo_utyil.xml');

function buscaSegmento($segmentos,$idseg){
	for($i=0;$i<$segmentos->count();$i++){
		$seg = $segmentos[$i];
		$x = $seg->attributes();
		if($x["id"] == $idseg){
			return $i;
		}
	}
}

function points2linestring($puntos){
	$linea = array();
	for($i=0;$i<$puntos->P->count();$i++){
		$coords = $puntos->P[$i];
		//print_r($coords);
		$linea[] = $coords["long"]." ".$coords["lat"];
	}
	
	return "LINESTRING(".implode(",",$linea).");";
}

$seg = buscaSegmento($a->Network->Segments->Segment,"S2");

echo "El segmento S2 se encuentra en la posicion: ".$seg;
$atributos = $a->Network->Segments->Segment[$seg]->attributes();
$linea = points2linestring($a->Network->Segments->Segment[$seg]->Points);
echo " Distancia: ".$atributos["length"]." Linea: ".$linea;

pg_query($condb,"SELECT ST_AsText(ST_Line_Interpolate_Point(linea,".(50/$atributos["length"]).")) as punto FROM (SELECT ST_GeomFromText('".$linea."',4326) as linea) as geom;");
//print_r($a->Network->Segments->Segment[$seg]->Points);
?>