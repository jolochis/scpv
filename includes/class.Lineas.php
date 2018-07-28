<?php

class Lineas{
	private $norte=null;
	private $sur=null;
	private $este=null;
	private $oeste=null;
	private $errorMsj = "";
	private $interLineas;
	private $interPuntos;
	private $seleccion;
	private $interIds;
	private $inter_osm_id;
	private $coleccionLineas;
	private $calle;
	
	/*private function setColeccion($coleccion){
		global $condb;
		
		$query = pg_query($condb,"Select ST_Union({$coleccion})");
	}*/
	
	public function setInterId($inter_id){
		$this->inter_osm_id = $inter_id;
	}
	
	public function setNorte($norte){
		$this->norte = $norte;
	}
	
	public function setSur($sur){
		$this->sur = $sur;
	}
	
	public function setEste($este){
		$this->este = $este;
	}
	
	public function setOeste($oeste){
		$this->oeste = $oeste;
	}
	
	
	public function getCarriles($idLinea){
		global $condb2;
		$tags = array();
		
		$query = "Select r.tags as tags from relations r
		join relation_members rm on rm.relation_id=r.id and rm.member_id ={$idLinea}";
		
		$results=pg_query($condb2,$query);
		if(pg_num_rows($results)>0){
			while($result = pg_fetch_array($results)){
				$tags[]=(array)$result["tags"];
			}
		}
	}
	
	public function getPuntosLinea($linea){
		global $condb;
				
		$q = pg_query($condb,"Select * from \"getPuntos\"('{$linea}');");
		if(pg_num_rows($q)>0){
			$lineaPuntos;
			$feature = array();
			
			while($results = pg_fetch_array($q))
			{
				$feature[] =array(
					"type" => "Feature",
					"properties" => array("angle"=>$results['angle'],"graphic"=>'imagenes/direction.png'),
					"geometry" =>(array)json_decode($results['punto'])
				);
			}
			$lineaPuntos = array(
				"type" => "FeatureCollection",
				"features" => $feature
			);
		}
		pg_free_result($q);
		
		return $lineaPuntos;
	}
	
	
	public function getPuntosSubLinea($line,$point1,$point2){
		global $condb;
		//echo$line; echo $point1; echo $point2;
		$q = pg_query($condb,"Select * from \"getPuntosSubLinea\"('{$point1}','{$point2}','{$line}');");
		if(pg_num_rows($q)>0){
			$subLinea;
			$feature = array();
			
			while($results = pg_fetch_array($q))
			{
				$feature[] =array(
					"type" => "Feature",
					"properties" => array("angle"=>$results['angle'],"graphic"=>'imagenes/direction.png'),
					"geometry" =>(array)json_decode($results['punto']),
					"angulo" => $results['angle']
				);
			}
			$subLinea = array(
				"type" => "FeatureCollection",
				"features" => $feature
			);
		}
		pg_free_result($q);
		
		return $subLinea;
	}
	
	public function comparaPunto($line,$point){
		global $condb;
		$query = pg_query($condb,"SELECT ST_Contains(ST_Buffer('{$line}'::geometry,4),'{$point}'::geometry) as valido");
		$result = pg_fetch_array($query);
		$valido =$result["valido"];
		pg_free_result($query);
		return $valido;
	}
	
	public function comparaLineas($line1,$line2){
		global $condb;
		$sentence = "Select ST_Contains(ST_Buffer('{$line2}'::geometry,4),'{$line1}'::geometry) as igual";
		
		$query = pg_query($condb,$sentence);
		$result = pg_fetch_array($query);
		$igual =array( 
			"igual"=>$result["igual"]
			);
		pg_free_result($query);
		return $igual;
	}
	
	public function consultaIntersecciones(){
		global $condb;
		
		if(!empty($this->inter_osm_id)){
		$box="ST_setSRID('BOX3D(".$this->oeste." ".$this->norte.", ".$this->este." ".$this->sur.")'::box3d, 900913)";
		$poly="ST_MakeEnvelope({$this->oeste},{$this->sur},{$this->este},{$this->norte},900913)";
			
			$q2 = pg_query($condb,"SELECT DISTINCT(osm_id), ST_AsGeoJSON(st_asText(ST_Intersection(way,(select St_Intersection({$poly},way) from planet_osm_line WHERE osm_id = {$this->inter_osm_id} )))) as interseccion ".
				", ST_AsGeoJSON(ST_AsText(ST_GeometryN(ST_Multi(St_Intersection({$poly},way)),1))) as calle, ST_AsText(way) as linea".
				",ST_AsText(ST_Intersection(way,(select way from planet_osm_line WHERE osm_id = {$this->inter_osm_id}))) as point".
				" FROM planet_osm_line".
				" WHERE ST_Intersects(St_Intersection({$poly},way),(Select way from planet_osm_line WHERE osm_id ={$this->inter_osm_id}))!='f'".
				" AND osm_id!={$this->inter_osm_id}".
				";");
			if(pg_num_rows($q2)>0)
			{
				$this->interLineas = array();
				$this->interPuntos = array();
				$Lineas = array();
				$Puntos = array();
				$coleccionLineas = array();
				$this->interIds = array();
				$this->coleccionLineas= "";
				while($resultados=pg_fetch_array($q2)){
				
					$Lineas[] = array(
						"type" => "Feature",
						"properties" => array("idlinea"=> $resultados["osm_id"],"geom" => (array) $resultados["calle"],"linea" => $resultados['linea'],"point" => $resultados["point"]),
						"geometry" => (array)json_decode($resultados["calle"])
					);
					
					$Puntos[] = array(
						"type" => "Feature",
						"properties" => array("idlinea"=> $resultados["osm_id"], "geom" => (array) $resultados["calle"],"linea" => $resultados['linea']),
						"geometry" => (array)json_decode($resultados["interseccion"])
					);
					
				}
				pg_free_result($q2);
				
				$this->interLineas = array(
					"type" => "FeatureCollection",
					"features" => $Lineas
				);
					
									
				$this->interPuntos = array(
					"type" => "FeatureCollection",
					"features" => $Puntos
				);
				
				return false;
			}
		}
		pg_free_result($q2);
		return true;
	}
	
	public function consultaSeleccion($idSeleccion){
		global $condb;
		$poly="ST_MakeEnvelope({$this->oeste},{$this->sur},{$this->este},{$this->norte},900913)";
		$q = pg_query($condb,"SELECT DISTINCT(osm_id), ST_AsText(St_Intersection({$poly},way)) as linea, ST_AsGeoJSON(st_asText(ST_GeometryN(ST_Multi(St_Intersection({$poly},way)),1))) as calle FROM planet_osm_line ".
			"WHERE". 
			" way && ST_setSRID('BOX3D(".$this->oeste." ".$this->norte.", ".$this->este." ".$this->sur.")'::box3d, 900913) AND ".
			" osm_id={$idSeleccion};");
			
		$result = pg_fetch_array($q);
		
		$geometria = array();
		$geometria[] = array(
			"type" => "Feature",
			"properties" => array("idlinea" => $result["osm_id"], "linea" => $result["linea"]),
			"geometry" => (array) json_decode($result["calle"])
		);
		
		$this->seleccion = array(
			"type" => "FeatureCollection",
			"features" => $geometria
		);
		
		pg_free_result($q);
		
	}
	
	public function consultarLineas(){
		global $condb;
		$poligono = "POLYGON((".$this->oeste." ".$this->norte.",".$this->oeste." ".$this->sur.",".$this->este." ".$this->sur.",".$this->este." ".$this->norte.",".$this->oeste." ".$this->norte."))";
		
		$q = pg_query($condb,"SELECT DISTINCT(osm_id),ST_AsGeoJSON(st_AsText(ST_Intersection(way,ST_GeomFromText('".$poligono."',900913)))) as geometria ,ST_AsText(way) as linea, ST_AsGeoJSON(st_asText(way)) as calle FROM planet_osm_line 
			WHERE way && ST_setSRID('BOX3D(".$this->oeste." ".$this->norte.", ".$this->este." ".$this->sur.")'::box3d, 900913);");

		
		if(pg_num_rows($q)>0){	
			$geometrias = array();
			while($resultados=pg_fetch_array($q)){
				$geometrias[] = array(
						"type" => "Feature",
						"properties" => array("idlinea" => (int)$resultados["osm_id"],"geom" => (array)json_decode($resultados["calle"]), "linea"=>$resultados["linea"], 
							"con" => "SELECT osm_id,ST_AsGeoJSON(st_asText(ST_Intersection(way,ST_GeomFromText('".$poligono."',900913)))) as geometria FROM planet_osm_line WHERE way && ST_setSRID('BOX3D(".$this->oeste." ".$this->norte.", ".$this->este." ".$this->sur.")'::box3d, 900913);"
							),
						"geometry" => (array)json_decode($resultados["geometria"])
					);
				}
			
			pg_free_result($q);
			
			$this->lineas = array(
					"type" => "FeatureCollection",
					"features" => $geometrias
				);
						
			return true;
			
		}
		else{
			$this->errorMsj = "No hay calles en esta seccion.".pg_last_error($condb);
			return false;
		}
	}
		
	public function getLineas(){
		return $this->lineas;
	}
	
	public function getError(){
		return $this->errorMsj;
	}
	
	public function getInterPuntos(){
		return $this->interPuntos;
	}
	
	public function getInterLineas(){
		return $this->interLineas;
	}
	
	public function getSeleccion(){
		return $this->seleccion;
	}
	
	public function getColeccionLineas(){
		return $this->coleccionLineas;
	}
}
?>
