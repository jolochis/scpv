<?php
include_once("includes/condb.php");
error_reporting(E_ALL);
if(isset($_POST['submit'])){
$verde = $_POST['verde'];
$rojo = $_POST['rojo'];
$amarillo = $_POST['amarillo'];
$d =$_POST['descripcion'];  
$lat = $_POST['lat'];
$lon = $_POST['lon'];
$elemento = $_POST['tipo'];
$aforoA = $_POST['autos'];
$aforoT = $_POST['tiempo'];


/*$arrreglo = array($elemento,$verde,$amarillo,$rojo,$lat,$lon);
echo "<pre>";
var_dump($ar);
echo "</pre";
*/
$ar = json_decode($_POST['tipo']);

foreach ($ar as $elementos) {
  switch ($elementos) {
    case 'paso':
      echo "paso";
      break;

    case 'tope':
    echo 'tope';
    break;

    case 'semaforo':
      echo "semaforo";
    default:
      # code...
      break;
  }
}
/*
if($elemento=='semaforo'){
  echo "<pre>";
  var_dump($_POST);
  echo "</pre>";
  $sql = "INSERT INTO elementos (tipo,rojo,amarillo,verde,lat,lon,descripcion )
                  values ('$elemento',$rojo,$amarillo,$verde,$lat,$lon,'$d');";
}
elseif($elemento =='aforos'){
$sql = "INSERT INTO elementos (tipo,lat,lon,aforo_auto,aforo_tiempo,descripcion )
                  values ('$elemento',$lat,$lon,$aforoA,$aforoT,'$d');";   
                  echo "aforo if";
                  }else{
                    echo "otro if";
                    $sql = "INSERT INTO elementos (tipo,lat,lon,descripcion )
                  values ('$elemento',$lat,$lon,'$d');";  
                  }
                  
$result = pg_query($condb, $sql);
if (!$result) {
    echo "Ocurrió un error.\n";
     
    exit;
  }
*/


}//if
?>