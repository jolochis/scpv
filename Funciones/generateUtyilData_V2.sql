DROP FUNCTION "generateUtylDatav2"(INTEGER, INTEGER);

/**
* Crea una tabla que contiene los segmentos de una propuesta
* @param {Integer} propuesta la primera variable corresponde al id de la propuesta 
* @param {Integer} pjin
* @return {Table} table tabla con la información de las conexiones de cada segmentos
* lonlat4 son las coordenadas en su projeccion original, lonlat9 son las coordenadas 
* del objeto en la projeccion 900913
*/
CREATE OR REPLACE FUNCTION "generateUtylDatav2"(IN propuesta INTEGER, IN pjin integer)
RETURNS TABLE(_name character varying(10), _lonlat4 text, _lonlat9 text, _length double precision, 
			  _segmentType character varying(30), _type character varying(30)) AS
			  
$BODY$DECLARE
	propuesta_ integer;
	_north text;
	_south text;
	_west  text;
	_east  text;
	bbox   text; -- será las coordenadas del cuadrante con la projección original
	boxx   text; -- serán las coordenadas del cuadrante con la projeccion 900913
	-- variables para las calles del cuadrante
	strasse  text; -- almacena las coordenadas de una calle
	bahnen integer; -- número de carriles de la calle principal	
	strasseid integer; -- contiene el id de "strasse"	
	auxid integer; -- auxiliar para el ide de strasse
	strassen refcursor; -- contiene toodas las calles del cuadrante
	weg text; --direccion
	direction integer;
	actSegment text; -- del segmento actual;
	-- para el controlde las interesecciones
	countInter integer default 1;
	countSegment integer default 1;
	punkte refcursor;
	punkt1 text; -- coordenada de un punto
	punkt2 text; -- coordenada de un punto
	c1 integer; -- numero De la calle intersectada
	c2 integer; -- numero de la calle intersactad secundaria
	auxpunkt text; -- punto de intersección auxiliar
	pos1 double precision; -- posición 
	pos2 double precision; -- posición
	auxpos1 double precision;
	auxpos2 double precision;
	type1 text;
	type2 text;
	stype1 text;
	stype2 text;
	S1 text; -- almacena las coordenadas del segmento que se está construyendo
	S2 text; -- almacena las coordenadas del segmento que se está construyendo
	S1name text;
	S2name text;
	I1 text;
	I2 text;
	I1name text;
	I2name text;
	m1 text[][];
	m2 text[][];
	
BEGIN
	
	propuesta_ := $1;
	IF (SELECT "idPropuesta" FROM segments WHERE "idPropuesta" = $1 LIMIT 1) IS NULL THEN
		-- se crean las tablas temponales que se usarán más adelante
		-- CREATE TEMPORARY TABLE "tempPunkte"(coor text,lanes integer,typeSegment text, typePosition text);
		CREATE TEMPORARY TABLE "tempMatriz"(name text,pos double precision, c1 integer, c2 integer, coor text,matriz text[][]);
		CREATE TEMPORARY TABLE "tempCuadrante"(coor text,osm_id integer, lanes integer,direction text);
		
		-- Crear el cuadrante de acuerdo a la configuración de la propuesta.
		SELECT maxx,maxy, minx, miny FROM propuestas WHERE id = $1 into _west, _north, _east, _south;
		SELECT CAST('BOX3D('|| _west || ' '||_north||','||_east||' '||_south||')' as text) into bbox;
		SELECT ST_ASTEXT(ST_Transform(ST_setSRID(bbox::box3d, $2),900913)) into boxx;
		
		-- Obtener todas las calles dentro de del cuadrante en una tabla temporal		
		INSERT INTO "tempCuadrante" 
			SELECT ST_ASTEXT((ST_DUMP(ST_Intersection(boxx::geometry,ST_ASTEXT(way)::geometry))).geom) as coor,
			osm_id as osm_id, COALESCE(lanes,1) as lanes, oneway as direction
			FROM planet_osm_line
			WHERE way && ST_setSRID(boxx::geometry,900913)
			AND ST_Intersects(boxx::geometry,ST_ASTEXT(way)::geometry)
			-- AND ST_ASTEXT(ST_Intersection(boxx::geometry, ST_ASTEXT(way)::geometry)) not like '%MULTI%'
			;
			
		-- selecciona las calles del cuadrante en un cursor
		OPEN strassen FOR SELECT * FROM "tempCuadrante";		
		
		-- busca las interesecciones de la calle
		LOOP
			-- TRUNCATE "tempPunkte";			
			fetch strassen into strasse, strasseid, bahnen, weg;
			-- hay calles?
			IF strasseid IS NULL THEN 
				EXIT;				
			ELSE
				-- detecta la dirección
					SELECT
						CASE WHEN p.weg = 'yes' then 1
							WHEN p.weg = 'no'  then 0
							ELSE -1
						END
					INTO direction FROM (select weg) as p;
				-- busca las intersecciones de la calle actual y las ordena de acuerdo a su posición				
				-- tabla temporal para almacenar las intersecciones de la calle actual, strasse				
				RAISE NOTICE 'Linea -- % ', (select strasse
				);
				INSERT INTO "tempPunkte1"
					SELECT ST_ASTEXT((ST_Dump(ST_Intersection(coor::geometry,strasse::geometry))).geom) as coor,
						lanes,'intersection','inter'
						FROM "tempCuadrante"
						WHERE osm_id != strasseid AND ST_Intersects(coor::geometry,strasse::geometry)
						;				
				-- punto inicio
				INSERT INTO "tempPunkte1" 
					SELECT(ST_ASTEXT(ST_startPoint(strasse::geometry)),bahnen,'intersection','init') 
					WHERE NOT EXISTS(SELECT coor FROM "tempPunkte1" WHERE coor like strasse);
				-- punto final
				INSERT INTO "tempPunkte1" 
					SELECT(ST_ASTEXT(ST_EndPoint(strasse::geometry)),bahnen,'intersection','end') 
					WHERE NOT EXISTS(SELECT coor FROM "tempPunkte1" WHERE coor like strasse);
				-- ordena los puntos de acuerdo a la posición
								
				
				RAISE NOTICE 'Linea 118 (%) ', (SELECT typePosition FROM "tempPunkte1" limit 1);
				
				OPEN punkte FOR SELECT coor,lanes,ST_Line_Locate_point(strasse::geometry,coor::geometry) as pos,
					typeSegment, typePosition 
				FROM "tempPunkte1" ;
				
				/* INIT Encontrar puntos */
				-- por cada punto encontrado
				RAISE NOTICE 'Linea 118 (%) ', 131;
				fetch punkte into I1, c1, pos1, stype1, type1;-- primer punto encontrado				
				RAISE NOTICE 'Linea 118 (%) ', 133;
				
				countInter := countInter +1;
				S1name := 'S'||countSegment;
				RAISE NOTICE 'Linea 118 (%) ', 137;
				LOOP
					RAISE NOTICE 'Linea 118 (%) ', 139;
					fetch punkte into I2, c2, pos2, stype2, type2;-- segundo punto encontrado
					RAISE NOTICE 'Linea 118 (%) ', 141;
					S2name := NULL;
					auxpos1 := pos1;
					auxpos2 := pos2;
					IF I2 is NULL THEN 
						--otra rutina
						EXIT;
					ELSE
						-- VAlida a I1
						IF (type1 like 'init' OR type1 like 'end') THEN
							m1 = createMatrix(bahnen,c1,c2,'null');
							I1name := 'I'||(countInter-1);
						ELSE
							SELECT matriz, name, pos into m1,I1name,auxpos1 FROM "tempMatriz" WHERE ST_Equals(coor::geometry,I1::geometry);
						END IF;
					
						-- verifica que I2 Ya fueagregado				
						SELECT matriz,name, pos into m2,I2name,auxpos2 FROM "tempMatriz" WHERE ST_Equals(coor::geometry,I2::geometry);
						IF I2name is NULL THEN
							I2name := 'I'||countInter;
							m2 := createMatrix(c1, c2, I2name);
							countInter = countInter + 1;
							--Crea el segmento S1
							SELECT ST_ASTEXT(ST_LineSubstring(strasse::geometry, auxpos1, auxpos2)) INTO actSegment;
							--por cada uno de los carriles encontrados
							FOR x IN 1..bahnen LOOP
								-- agrega el segmento. Sí no existe
								INSERT INTO segments
									SELECT strasseid,(S1name||'_'||x)::text,'segment','inter', actSegment,
										$1, ST_Length(ST_Transform(ST_SetSRID(actSegment::geometry,900913),26986))
									WHERE NOT EXISTS(SELECT coordinates FROM segments WHERE NOT ST_Equals(actSegment::geometry,coordinates));
								-- crea la relación
								INSERT INTO "segmentConnections" values(propuesta_::Integer,(S1name||'_'||x)::text, m2[x][1],'NS');
								INSERT INTO "segmentConnections" values(propuesta_::Integer,(S1name||'_'||x)::text, m1[x][c2],'NS');
								INSERT INTO "segmentConnections"
									("idPropuesta","segmentName","segmentConnection","connectionType") 
									SELECT $1 , (S1name||'_'||x)::text, S2name, 'NC' WHERE EXISTS (SELECT S2name);
								-- crea objeto direccion
								/** INIT direccion**/
								-- select * FROM createDirection($1, strasseid,m2[x][1], m1[x][c2]);
								/** END direccion**/
								S2name := (S1name||'_'||x)::text;
							END LOOP;
							
						ELSE
							FOR x IN 1..bahnen LOOP
								-- agrega el segmento. Sí no existe
								INSERT INTO segments
									SELECT strasseid, (S1name||'_'||x)::text, 'segment','inter', actSegment,
										$1, ST_Length(ST_Transform(ST_SetSRID(actSegment::geometry,900913),26986))
									WHERE NOT EXISTS(SELECT pos FROM segments WHERE NOT ST_Equals(actSegment::geometry,coor));
								-- crea la relación
								INSERT INTO "segmentConnections" values(propuesta_::Integer,(S1name||'_'||x)::text, m1[1][x],'NS');
								INSERT INTO "segmentConnections" values(propuesta_::Integer,(S1name||'_'||x)::text, m1[c2][x],'NS');
								INSERT INTO "segmentConnections" (SELECT(propuesta_::Integer,(S1name||'_'||x)::text, S2name,'NC') 
								WHERE EXISTS(SELECT S2name));
								/** INIT direccion**/
								-- select * FROM createDirection($1, strasseid, m1[1][x], m1[c2][x]);
								/** END direccion**/
								S2name := (S1name||'_'||x)::text;
								
							END LOOP;							
						END IF;
						
					END IF;					
					pos1 = pos2;
					I1 = I2;					
				END LOOP;/* END Encontrar puntos */
			 
			END IF;
			
		END LOOP;
		
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