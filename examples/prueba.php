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

if($lat!=null && $lon!=null){
    echo "coordenadas";
}else{
    echo "no hay ,joven";
}
echo "<pre>";
var_dump($objeto);
echo "</pre>";
$id = 1;
$doc = new DOMDocument('1.0','UTF-8');
$doc->formatOutput = true;

$UTYiL = $doc->createElement('UTYil');
$UTYiL = $doc->appendChild($UTYiL);
$conts = $doc->createElement('Segmento');
$conts = $UTYiL->appendChild($conts);
$conts->setAttribute("id",$id);
$conts->setAttribute("type", 'EsperaVariable');
$conection = $doc->createElement('connection');
$conection = $conts->appendChild($conection);


$latitud = $doc->createElement('latitud',$lat);
$latitud = $UTYiL->appendChild($latitud);
$longitud = $doc->createElement('longitud',$lon);
$longitud = $UTYiL->appendChild($longitud);
$element = $doc->createElement('Elemento', $elemento);
$element = $UTYiL->appendChild($element);   
$descripcion = $doc->createElement('Descripcion', $desc);
$descripcion = $UTYiL->appendChild($descripcion);
//aforos
$aforo = $doc->createElement('Aforos');
$aforo = $UTYiL->appendChild($aforo);
$aforo->setAttribute('Autos', $aforoA);
$aforo->setAttribute('Tiempo',$aforoT);

//semaforos
$semaforo = $doc->createElement('Semaforo');
$semaforo = $UTYiL->appendChild($semaforo);
$semaforo->setAttribute('rojo',$rojo);
$semaforo->setAttribute('amarillo',$amarillo);
$semaforo->setAttribute('verde',$verde);


$string_value = $doc->saveXML();
$doc->save("util.xml");
?>