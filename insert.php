<?php
include_once("includes/condb.php");
error_reporting(E_ALL);
echo "entra php";

$verde = $_POST['verde'];
$rojo = $_POST['rojo'];
$amarillo = $_POST['amarillo'];
$d =$_POST['descripcion'];  
$lat = $_POST['lat'];
$lon = $_POST['lon'];
$elemento = $_POST['tipo'];
$aforoA = $_POST['autos'];
$aforoT = $_POST['tiempo'];

echo "submit";
  switch ($elemento) {

    case 'paso':
      $sql = "INSERT INTO elementos (tipo,lat,lon,descripcion)
                      values('$elemento','$lat','$lon','$d');";
      break;

    case 'tope':
    $sql = "INSERT INTO elementos (tipo,lat,lon,descripcion)
                      values('$elemento','$lat','$lon','$d');";
    break;

    case 'semaforo':
      $sql = "INSERT INTO elementos (tipo,rojo,amarillo,verde,lat,lon,descripcion)
                      values('$elemento','$rojo','$amarillo','$verde','$lat','$lon','$d');";
    break;
    case 'aforos':
     
      $sql = "INSERT INTO elementos (tipo,lat,lon,aforo_auto,aforo_tiempo,descripcion)
                      values('$elemento','$lat','$lon','$aforoA','$aforoT','$d');";
    break;
    case ( 'semaforo' && 'aforo'):
      $sql = "INSERT INTO elementos (tipo,rojo,amarillo,verde,lat,lon,aforo_auto,aforo_tiempo,descripcion)
                      values('$elemento','$rojo','$amarillo','$verde','$lat','$lon','$aforoA','$aforoT','$d');";
    break;
             
    default:
      # code...
      break;
  
}$result = pg_query($condb, $sql);
if (!$result) {
    echo "Ocurrió un error.\n";
     
    exit;
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
                  
*/


//if
?>