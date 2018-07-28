-- Function: "generateUtyilData"(numeric, numeric, numeric, numeric, integer, integer)

-- DROP FUNCTION "generateUtyilData"(numeric, numeric, numeric, numeric, integer, integer);

CREATE OR REPLACE FUNCTION "generateUtyilData"(IN propuesta numeric, IN pjin Integer)
  RETURNS TABLE(_name character varying(10), _lonlat4 text, _lonlat9 text, _length double precision,_segmentType character varying(30), _type character varying(30)) AS
$BODY$declare
	intername text;
	segment_linea text;
	pos numeric;
	auxpos numeric;
	auxintername text;
	auxtype text;
	auxpoint text;
	auxname text;
	direction integer;
	idlinea integer;
	idlinea2 integer;
	auxidlinea integer;
	auxidlinea2 integer;
	auxidinter integer;
	auxid integer;
	lineas refcursor;
	auxlineas refcursor;
	inters refcursor;
	auxinter refcursor;
	linea text;
	auxlinea text;
	boxx text;
	bbox text;
	_north text;
	_west text;
	_east text;
	_south text;
BEGIN
	/**
	* Busca la configuración de la propuesta guardada y extrae la información del cuadrante y la agrega a las bariables
	* west,north,east y south, creando un rectángulo (BOX3D) llamdo bbox
	*/
	IF (Select "idPropuesta" from segments where "idPropuesta" = $1 LIMIT 1) IS NULL THEN	
		SELECT maxx,maxy,minx,miny FROM propuestas WHERE id = $1 into _west,_north,_east,_south;
		SELECT Cast('BOX3D('||
			Cast(_west as text)||' '||
			Cast(_north as text)||','||
			Cast(_east as text)||' '||
			Cast(_south as text)||')' as text) into bbox;
		
		/**
		*Transforma el bbox al la proyeccion 900913
		*/
		select ST_AsText(ST_transform(st_setSRID(Cast(bbox as text)::box3d,$2),900913))into boxx;
		
		/**
		* obtiene todas las calles que se encuentran dentro del cuadrante bbox y las almacen en lineas
		*/
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
				-- se obtienen los puntos con los que se intersecta una calle
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
						SELECT "segmentName" into auxname from segments WHERE cast(coordinates as text) like cast(auxinter as text)
							AND "idPropuesta" = $1;
						IF auxname IS NOT NULL THEN
							IF (SELECT "segmentName" from segments WHERE cast(coordinates as text) like cast(auxinter as text)
								AND "idPropuesta" = $1 AND "idCalle" = idlinea) 
								IS NULL THEN
								--INSERT INTO temp_intersections(idline1,idline2,"type",coordinates,"name") 
								INSERT INTO segments("idCalle","segmentName","segmentType","type",coordinates,"idPropuesta",distance)
								VALUES(idlinea, auxname, 'intersection' ,cast('inter' as text),auxinter, $1,0.0);
							END IF;
						ELSE
							auxidinter = auxidinter + 1;
							--INSERT INTO temp_intersections(idline1,idline2,"type",coordinates,"name") 
							--VALUES(idlinea,auxidlinea,cast('inter' as text),auxinter,cast('I'||auxidinter as text));
							INSERT INTO segments("idCalle","segmentName","segmentType","type",coordinates,"idPropuesta",distance)
							VALUES(idlinea, cast('I'||auxidinter as text), 'intersection' ,cast('inter' as text),auxinter, $1,0.0);
						END IF;
					
					END IF;
				END LOOP;
				CLOSE auxlineas;
				--
				--Verifica si el punto inicial corresponde a otra interseción ya encontrada para agregar un sefmentofo de tipo init
				--
				SELECT ST_AsText(ST_StartPoint(linea::geometry)) into auxpoint;
				SELECT "segmentName" into auxname from segments WHERE cast(coordinates as text) like cast(auxpoint as text) 
					AND "idPropuesta" = $1;
				
				IF auxname ISNULL THEN
					auxidinter = auxidinter + 1;
					INSERT INTO segments("idCalle","segmentName","segmentType","type",coordinates,"idPropuesta",distance)
					values(idlinea, 'null'/*cast('I'||auxidinter as text)*/, 'intersection',cast('init' as text),
						ST_AsText(ST_StartPoint(linea::geometry)),$1, 0.0
					);
				END IF;
				
				---
				---Verifica  si el punto final de la calle ya se encuentra en otra intersección
				---
				SELECT ST_AsText(ST_EndPoint(linea::geometry)) into auxpoint;
				SELECT "segmentName" into auxname from segments WHERE cast(coordinates as text) like cast(auxpoint as text)
					AND "idPropuesta" = $1;
		
				IF auxname ISNULL THEN
					auxidinter = auxidinter + 1;
					INSERT INTO segments("idCalle","segmentName","segmentType","type",coordinates,"idPropuesta",distance)
					values(idlinea,'null' /*cast('I'||auxidinter as text)*/, 'interseccion',cast('end' as text),
						ST_AsText(ST_EndPoint(linea::geometry)), $1, 0.0
					) ;
				END IF;
				
			END IF;
		END LOOP;
		
		FETCH FIRST FROM lineas into linea,idlinea;
		auxidinter = 1;
		
		LOOP
			IF idlinea ISNULL THEN
				exit;
			ELSE
				-- Se obtienen la intersecciones ya indentificadas por calle, de acuerdo a la posición 
				-- en que se encuentran, se ordenan.
				OPEN inters FOR SELECT "segmentName",
				ST_Line_Locate_Point(linea::geometry,coordinates::geometry)
				FROM segments 
				where "idCalle" = idlinea AND "segmentName" NOT LIKE '%S%' AND "idPropuesta" = $1
				ORDER BY (ST_Line_Locate_Point(linea::geometry,coordinates::geometry)) asc;
				
				-- primer punto de la calle
				fetch inters into intername,pos;
				IF intername ISNULL THEN
					exit;
				ELSE
					LOOP
						-- Siguiente punto de la misma calle
						fetch inters into auxintername,auxpos;
						IF (auxintername ISNULL) THEN
							exit;
						ELSIF pos != auxpos THEN
							-- Se obtiene la sublinea que compone al segmento a crear, 
							-- se usa al las intersecciones que delimitan la calle
							SELECT ST_AsText(ST_line_Substring(linea::geometry,pos,auxpos)) into segment_linea;
							
							auxtype='inter';
							
							-- se omite el paso de identificar si es o no pozo o fuente
							-- este paso debe ser realizado por el simulador
							
							SELECT "segmentName" into auxname from segments WHERE cast(coordinates as text) like cast(segment_linea as text)
								AND "idPropuesta" = $1;
							
							IF auxname IS NULL THEN
								--Inserta los segmentos
								INSERT INTO segments("idCalle","segmentName","segmentType","type",coordinates,"idPropuesta","distance")
									VALUES (idlinea, cast('S'||auxidinter as text), 'segment', auxtype, segment_linea,
									$1,ST_Length(ST_Transform(ST_SetSRID(segment_linea::geometry,900913),26986)));
									
								INSERT INTO "segmentConnections"
									VALUES($1, cast('S'||auxidinter as text), intername,'NS');
								
								INSERT INTO "segmentConnections"
									VALUES($1, cast('S'||auxidinter as text), auxintername,'NS');
									
								--(idlinea, cast('S'||auxidinter as text),auxintername,intername,segment_linea,
								--ST_Length(ST_Transform(ST_SetSRID(segment_linea::geometry,900913),26986)),auxtype);
								
								-- aqui ver el sentido de la calle
								-- determina la dirección de la calle
								SELECT
									CASE WHEN oneway = 'yes' then 1
										 WHEN oneway = 'no'  then 0
										 ELSE -1
									END
								INTO direction
								FROM (Select distinct(osm_id),oneway FROM planet_osm_line where osm_id = idlinea) as p;
								
								
								-- si es oneway
								IF direction = 1 THEN
									INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
										VALUES($1, 'direction', 'POINT(0 0)'::geometry, idlinea, 'POINT(0 0)'::geometry, idlinea, cast('S'||auxidinter as text))       
									RETURNING id INTO auxid;
									
									INSERT INTO configuracion(idobject,init,tend,attr)
										VALUES(auxid, 0, '$', auxintername);
								END IF;
								
								-- si es reverse
								IF direction = 0 THEN
									INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
										VALUES($1, 'direction', 'POINT(0 0)'::geometry, idlinea, 'POINT(0 0)'::geometry, idlinea, cast('S'||auxidinter as text))       
										RETURNING id INTO auxid;
									
									INSERT INTO configuracion(idobject,init,tend,attr)
										VALUES(auxid, 0, '$', intername);
									
								END IF;
								
								-- si es doble sentido
								IF direction = -1 THEN
									INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
										VALUES($1, 'direction', 'POINT(0 0)'::geometry, idlinea, 'POINT(0 0)'::geometry, idlinea, cast('S'||auxidinter as text))       
									RETURNING id INTO auxid;
									
									INSERT INTO configuracion(idobject,init,tend,attr)
										VALUES(auxid, 0, '$', auxintername);
									
									INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
										VALUES($1, 'direction', 'POINT(0 0)'::geometry, idlinea, 'POINT(0 0)'::geometry, idlinea, cast('S'||auxidinter as text))       
										RETURNING id INTO auxid;
									
									INSERT INTO configuracion(idobject,init,tend,attr)
										VALUES(auxid, 0, '$', intername);
									
								END IF;
							END IF; --auxname
							-- Se pasan los valores del punto actual						
							pos = auxpos;
							intername = auxintername;
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
	END IF;
	return QUERY 
		SELECT s."segmentName" as "name",
			ST_AsText(St_transform(St_setSRID(coordinates::geometry,900913),4326)) as lonlat,
			coordinates, "distance" as "length", s."segmentType","type" FROM segments s, "segmentConnections" sc 
			WHERE "type" like 'inter' AND s."segmentName" like 'I%' AND s."idPropuesta" = $1 
			AND sc."segmentConnection" = s."segmentName"
		union SELECT "segmentName" as "name",
			ST_AsText(St_transform(St_setSRID(coordinates::geometry,900913),4326)) as lonlat,
			coordinates,"distance" as "length", "segmentType","type" from segments
			WHERE "segmentName" like 'S%' AND "idPropuesta" = $1
		
		ORDER BY "name"
		;	
	
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION "generateUtyilData"(numeric, Integer)
  OWNER TO postgres;