CREATE OR REPLACE FUNCTION "getSegmentos"(IN west numeric, IN north numeric, IN east numeric, IN south numeric, IN pjin integer)
  RETURNS TABLE(_name text, _lonlat4 text, _lonlat9 text, _to text, _from text, _length numeric, _type text) AS
$BODY$declare
	intername text;
	segment_linea text;
	pos numeric;
	auxpos numeric;
	auxintername text;
	auxtype text;
	auxpoint text;
	auxname text;
	idlinea integer;
	idlinea2 integer;
	auxidlinea integer;
	auxidlinea2 integer;
	auxidinter integer;
	lineas refcursor;
	auxlineas refcursor;
	inters refcursor;
	auxinter refcursor;
	linea text;
	auxlinea text;
	boxx text;
	_north text;
	_west text;
	_east text;
	_south text;
BEGIN
	TRUNCATE temp_intersections;
	TRUNCATE temp_segmentos;
	select ST_AsText(ST_transform(st_setSRID(Cast('BOX3D('||$1||' '||$2||','||$3||' '||$4||')' as text)::box3d,$5),900913))into boxx;
	--select Cast('BOX3D('||$1||' '||$2||','||$3||' '||$4||')' as text) into boxx;
	
	OPEN lineas FOR SELECT ST_AsText(
	(ST_Dump(St_Intersection(boxx::geometry,ST_AsText(way)::geometry))).geom),osm_id
	FROM planet_osm_line 
	WHERE way && st_setSRID(boxx::geometry,900913)
	AND St_Intersects(boxx::geometry,ST_AsText(way)::geometry)
	AND	ST_AsText(St_Intersection(boxx::geometry,ST_AsText(way)::geometry)) not like '%MULTI%'
	;
	--
	-- Localiza las calles dentro del cuadrante
	--
	auxidinter = 0;
	LOOP	
		fetch lineas into linea,idlinea;
		
		IF linea ISNULL THEN
			exit;
		ELSE
			--
			--se obtienen los puntos con los que se intersecta una calle
			--
			OPEN auxlineas FOR SELECT ST_AsText(
			(ST_Dump(ST_Intersection(ST_AsText(way)::geometry,linea::geometry))).geom),
				osm_id
				FROM planet_osm_line
				WHERE --way && st_setSRID(boxx::box3d,900913) 
				ST_intersects(ST_AsText(way)::geometry,linea::geometry) 
				AND osm_id !=idlinea
				AND ST_AsText(ST_Intersection(ST_AsText(way)::geometry,linea::geometry)) not Like '%MULT%'
				--AND NOT(st_intersects(ST_StartPoint(linea::geometry),ST_Intersection(ST_AsText(way)::geometry,linea::geometry))
				--	OR st_intersects(ST_EndPoint(linea::geometry),ST_Intersection(ST_AsText(way)::geometry,linea::geometry)))
				;
				
			LOOP
				fetch auxlineas into auxinter,auxidlinea;
				IF auxidlinea ISNULL THEN
					exit;
				ELSE
					--
					--Veifica si alguno de las intersecciones ya fué agregada, de serlo 
					--se guarda la intersección con el mismo nombre de la intersección en ese punto
					--
					SELECT "name" into auxname from temp_intersections WHERE cast(interpunto as text) like cast(auxinter as text);
					IF auxname IS NOT NULL THEN
						INSERT INTO temp_intersections(idline1,idline2,"type",interpunto,"name") 
						VALUES(idlinea,auxidlinea,cast('inter' as text),auxinter,
						auxname);
					ELSE
						auxidinter = auxidinter + 1;
						INSERT INTO temp_intersections(idline1,idline2,"type",interpunto,"name") 
						VALUES(idlinea,auxidlinea,cast('inter' as text),auxinter,
						cast('I'||auxidinter as text));
					END IF;
				
				END IF;
			END LOOP;
			CLOSE auxlineas;
			--
			--Verifica si el punto inicial corresponde a otra interseción ya encontrada
			--
			SELECT ST_AsText(ST_StartPoint(linea::geometry)) into auxpoint;
			SELECT "name" into auxname from temp_intersections WHERE cast(interpunto as text) like cast(auxpoint as text);
			
			IF auxname ISNULL THEN
				auxidinter = auxidinter + 1;
				INSERT INTO temp_intersections(idline1,idline2,"type",interpunto,"name") 
				values(idlinea,idlinea,cast('init' as text),ST_AsText(ST_StartPoint(linea::geometry)),
				cast('I'||auxidinter as text)) ;
			END IF;
			
			---
			---Verifica  si el punto final de la calle ya se encuantra en otra intersección
			---
			SELECT ST_AsText(ST_EndPoint(linea::geometry)) into auxpoint;
			SELECT "name" into auxname from temp_intersections WHERE cast(interpunto as text) like cast(auxpoint as text);
	
			IF auxname ISNULL THEN
				auxidinter = auxidinter + 1;
				INSERT INTO temp_intersections(idline1,idline2,"type",interpunto,"name") 
				values(idlinea,idlinea,cast('end' as text),ST_AsText(ST_EndPoint(linea::geometry)),
				cast('I'||auxidinter as text)) ;
			END IF;
			
		END IF;
	END LOOP;
	
	FETCH FIRST FROM lineas into linea,idlinea;
	auxidinter = 1;
	
	LOOP
		IF idlinea ISNULL THEN
			exit;
		ELSE
			OPEN inters FOR SELECT "name",
			ST_Line_Locate_Point(linea::geometry,interpunto::geometry),idline2
			FROM temp_intersections 
			where idline1 = idlinea
			ORDER BY (ST_Line_Locate_Point(linea::geometry,interpunto::geometry)) asc;

			fetch inters into intername,pos,idlinea2;
			IF intername ISNULL THEN
				exit;
			ELSE
				LOOP
					fetch inters into auxintername,auxpos,auxidlinea2;
					IF (auxintername ISNULL) THEN
						exit;
					ELSE
						SELECT ST_AsText(ST_line_Substring(linea::geometry,pos,auxpos)) into segment_linea;
						
						auxtype='inter';
						if(Select interpunto from temp_intersections
							WHERE ST_intersects(interpunto::geometry,ST_EndPoint(segment_linea::geometry))
							and "type" like 'end' ) IS NOT NULL THEN 
							auxtype='pozo';
						ELse					
						
							if(Select interpunto from temp_intersections
								WHERE ST_intersects(interpunto::geometry,ST_StartPoint(segment_linea::geometry))
								and "type" like 'init')IS NOT NULL THEN 
								auxtype='fuente';
							END IF;
						ENd IF;
						
						INSERT INTO temp_segmentos 
						VALUES(cast('S'||auxidinter as text) ,auxintername,intername,segment_linea,
						ST_Length(ST_Transform(ST_SetSRID(segment_linea::geometry,900913),26986)),auxtype);
						
						UPDATE temp_intersections SET "to"=cast('S'||auxidinter as text) 
						WHERE name like intername AND idline2 = idlinea2 and idline1 = idlinea
						AND "type" like 'inter' ;
						
						UPDATE temp_intersections SET "from"=cast('S'||auxidinter as text)
						WHERE name like auxintername AND idline2 = auxidlinea2 AND idline1 = idlinea
						AND "type" like 'inter' ;
						pos = auxpos;
						intername = auxintername;
						idlinea2=auxidlinea2;
						auxidinter = auxidinter +1;						
					END IF;
				
				END LOOP;
				CLOSE inters;
				
			END IF;
			FETCH lineas into linea,idlinea;
		END IF;
	
	END LOOP;
	CLOSE lineas;
	--4326
	return QUERY 
		SELECT "name",ST_AsText(St_transform(St_setSRID(interpunto::geometry,900913),4326)) as lonlat,interpunto,"to","from",0 as "length","type" FROM temp_intersections WHERE "type" like 'inter'
		union SELECT idsegmento as "name",
		ST_AsText(St_transform(St_setSRID(coordenadas::geometry,900913),4326)) as lonlat,coordenadas,"to","from",longitud as "length","type" from temp_segmentos
		WHERE "from" not like "to"
		
		ORDER BY "name"
		;	
	
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION "getSegmentos"(numeric, numeric, numeric, numeric, integer)
  OWNER TO postgres;