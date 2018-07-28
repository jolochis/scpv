<?php
error_reporting(E_ALL);

class Resultados{
	private $archivo = "simulador/resultados.txt";
	private $inicio = 0;
	private $numero = -1;
	private $resultados = array(); //idObjeto,tipoObjeto,latObj,longObj
	private $errorMsj = "";
	//private $interpolaciones = 0;
	private $segmentos = array(
		0 => array(
			"ini" => "-103.354526,20.677178",
			"fin" => "-103.347659,20.677338",
			"tam" => 132
		),
		4 => array(
			"ini" => "-103.347359,20.677419",
			"fin" => "-103.340685,20.677514",
			"tam" => 132
		),
		35 => array(
			"ini" => "-103.347766,20.682718",
			"fin" => "-103.347466,20.677378",
			"tam" => 132
		),
		5 => array(
			"ini" => "-103.347487,20.677368",
			"fin" => "-103.347327,20.67522",
			"tam" => 132
		),
	);

	public function setArchivo($archivo){
		$this->archivo = $archivo;
	}
	
	public function setInicio($i){
		$this->inicio = $i;		
	}
	
	public function setNumero($n){
		$this->numero = $n;		
	}
	
	private function AplicaRestricciones(){
		if(empty($this->archivo)){
			$this->errorMsj = "No has escrito el nombre del archivo";
			return false;
		}
		if(!file_exists($this->archivo)){
			return false;
		}
		if(!is_readable($this->archivo)){
			$this->errorMsj = "El archivo no se puede leer";
			return false;
		}
		if(((int)$this->inicio) < 0){
			$this->errorMsj = "No has establecido el inicio del rango de resultados";
			return false;
		}
		if(((int)$this->numero) < 0){
			$this->errorMsj = "No has establecido el numero de resultados que quieres obtener.";
			return false;
		}
		if(!is_int($this->inicio)){
			$this->errorMsj = "El numero de inicio que escribiste no es correcto";
			return false;
		}		$interpolaciones = 0;
		if(!is_int($this->numero)){
			$this->errorMsj = "El numero de resultados que quieres obtener no es correcto";
			return false;
		}
		return true;
	}
	
	public function leerArchivo(){
		if(!$this->AplicaRestricciones()){
			return false;
		}

		$a = fopen($this->archivo,"r");
		$linea= 0;
		while(($evento = fgets($a))!== false){
			if($linea >= ($this->inicio+1)  && $linea < ($this->inicio+1+$this->numero)){
				//objectID,evTime,segment,evPos,evType,
				$e = explode(",",$evento);
				$coordenadas = $this->interpolacionSegmento((int)$e[2],(float)$e[3]);
				
				if(strpos($e[0],"S") === false){
					//es evento de auto
					//idObjeto,tipoObjeto,latObj,longObj
					$this->resultados[]=array(
						"idObjeto" => (int) $e[0],
						"tipoObjeto" => 1,
						"latObj" => $coordenadas["latitud"],
						"longObj" => $coordenadas["longitud"],
						//"esInterpolacion"  => $coordenadas["interpolacion"],
						"tiempo" => $e[1]
					);
				}
				else{
					//es evento de semaforo.
					//idObjeto,tipoObjeto,latObj,longObj
					$this->resultados[]=array(
						"idObjeto" => (int) str_replace("S","",$e[0]),
						"tipoObjeto" => 2,
						"latObj" => $coordenadas["latitud"],
						"longObj" => $coordenadas["longitud"],
						//"esInterpolacion"  => $coordenadas["interpolacion"],
						"tiempo" => $e[1]
					);
				}
				//$this->interpolaciones = $coordenadas["interpolacion"]? $this->interpolaciones+1 : $this->interpolaciones;
			}
			
			$linea++;
		}
		fclose($a);
		return true;
	}
	
	private function interpolacionSegmento($idsegmento,$posicion){
		$retorno = array();
		if(array_key_exists((int)$idsegmento,$this->segmentos)){
			$inicio = explode(",",$this->segmentos[$idsegmento]["ini"]); 
			$fin = explode(",",$this->segmentos[$idsegmento]["fin"]);
			$residuolong = (float) (((float)$fin[0]) - ((float)$inicio[0]));
			$residuolat = (float)  (((float)$fin[1]) - ((float)$inicio[1]));
			
			$reglatreslong = (((int)$posicion)*$residuolong)/$this->segmentos[$idsegmento]["tam"];
			$reglatreslat  = (((int)$posicion)*$residuolat )/$this->segmentos[$idsegmento]["tam"];
			
			$retorno = array(
				"latitud" => (float)( ((float) $inicio[1] ) + $reglatreslat ),
				"longitud" => (float)( ((float) $inicio[0] ) + $reglatreslong ),
				"interpolacion" => true
			);
			
			
			/*if($residuolat < 0) $retorno["latitud"] = (float)( ((float) $fin[1] ) + $residuolat );
			else $retorno["latitud"] = (float)( ((float) $inicio[1] ) + $residuolat );
			
			if($residuolong < 0) $retorno["longitud"] = (float)( ((float) $fin[0] ) + $residuolong );
			else $retorno["longitud"] = (float)( ((float) $inicio[0] ) + $residuolong );
			$retorno["interpolacion"] = true;*/
			
		}
		else{
			$retorno = array(
				"latitud" => (float) (20+(mt_rand(50000,72000)*0.00001)),
				"longitud" => (float) (-103-(mt_rand(21000,40000)*0.00001)),
				"interpolacion" => false
			);
		}
		
		return $retorno;
	}
	
	public function getError(){
		return $this->errorMsj;
	}
	
	public function getResultados(){
		return $this->resultados;
	}
	
	/*public function getInterpolaciones(){
		return $this->interpolaciones;
	}*/
}
/*$json=array();
$resultados = new Resultados();
$resultados->setInicio(0);
$resultados->setNumero(500);
if(!$resultados->leerArchivo()){
	$json["errorMsj"] = $resultados->getError();
}
else{
	$json = array(
				"resultados" => $resultados->getResultados(),
				"errorMsj" => ""
	);
}

print_r($json);
*/
?>