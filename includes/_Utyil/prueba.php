<?php
$lat = $_POST['lat'];
$lon = $_POST['lon'];
$elemento = $_POST['estatico'];
$desc = $_POST['descripcion'];
$aforoA = $_POST['autos'];
$aforoT = $_POST['tiempo'];
$verde = $_POST['verde'];
$rojo = $_POST['rojo'];
$amarillo = $_POST['amarillo'];


$objeto = (object) array(
    'lat' => $lat,
    'lon' => $lon,
    'elemento' => $elemento,
    'descripcion' => $desc,
    'autos' => $aforoA,
    'tiempo' => $aforoT,
    'rojo' => $rojo,
    'amarillo' => $amarillo,
    'verde' => $verde,
);


echo "<pre>";
var_dump($objeto);
echo "</pre>";


?>