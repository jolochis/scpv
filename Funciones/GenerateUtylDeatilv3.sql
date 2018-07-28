	-- Function: "generateUtylDatav2"(integer, integer)

	DROP FUNCTION "tempp"(integer, integer);

	CREATE OR REPLACE FUNCTION "tempp"(IN propuesta integer, IN pjin integer)
	  RETURNS TABLE(_name character varying, _lonlat4 text, _lonlat9 text, _length double precision, _segmenttype character varying, _type character varying) AS
	$BODY$DECLARE
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
		pos1 float; -- posición 
		pos2 float; -- posición
		auxpos1 float ;
		auxpos2 float ;
		type1 text;
		type2 text;
		stype1 text;
		stype2 text;
		S1 text; -- almacena las coordenadas del segmento que se está construyendo
		S2 text; -- almacena las coordenadas del segmento que se está construyendo
		S1name text;
		S2name text;
		interSegment text;
		I1 text;
		I2 text;
		I1name text;
		I2name text;
		m1 text[][];
		m2 text[][];
		
	BEGIN
		
		
		IF (SELECT "idPropuesta" FROM segments WHERE "idPropuesta" = $1 LIMIT 1) IS NULL THEN
			-- se crean las tablas temponales que se usarán más adelante
			-- CREATE  TABLE "tempPunkte"(coor text,lanes integer,typeSegment text, typePosition text, idCalle integer) ;
			-- CREATE  TABLE "tempMatriz"(name text,pos double precision, c1 integer, c2 integer, coor text,matriz text[][]);
			-- CREATE  TABLE "tempCuadrante"(coor text,osm_id integer, lanes integer,direction text);
			truncate table "tempPunkte";
			truncate table "tempCuadrante";
			truncate table "tempMatriz";
			truncate table "segments";
			truncate table "segmentConnections";
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
				S1name:= ('S'||countSegment)::text;				
				-- RAISE warning '---Segment, %',S1name;
				fetch strassen into strasse, strasseid, bahnen, weg;
				-- hay calles?
				IF strasseid IS NULL THEN 
					EXIT;				
				ELSE
					countSegment:=countSegment + 1;
					truncate table "tempPunkte";
					-- detecta la dirección
						SELECT
							CASE WHEN p.weg = 'yes' then 1
								WHEN p.weg = 'no'  then 0
								ELSE -1
							END
						INTO direction FROM (select weg) as p;
					-- busca las intersecciones de la calle actual y las ordena de acuerdo a su posición				
					-- tabla temporal para almacenar las intersecciones de la calle actual, strasse
					
					INSERT INTO "tempPunkte"
						SELECT ST_ASTEXT((ST_Dump(ST_Intersection(coor::geometry,strasse::geometry))).geom) as coor,
							lanes,'intersection','inter',osm_id,strasseid
							FROM "tempCuadrante"
							WHERE osm_id != strasseid AND ST_Intersects(coor::geometry,strasse::geometry) 							
							;
					-- RAISE warning 'idUno:% ',strasseid;
					-- punto inicio
					INSERT INTO "tempPunkte" 
						SELECT ST_ASTEXT(ST_startPoint(strasse::geometry)),bahnen,'intersection','init'
						WHERE NOT EXISTS(SELECT coor FROM "tempPunkte" WHERE coor like ST_ASTEXT(ST_startPoint(strasse::geometry)));
					--punto final
					INSERT INTO "tempPunkte" 
						SELECT ST_ASTEXT(ST_EndPoint(strasse::geometry)),bahnen,'intersection','end'
						WHERE NOT EXISTS(SELECT coor FROM "tempPunkte" WHERE coor like ST_ASTEXT(ST_EndPoint(strasse::geometry)));
					--ordena los puntos de acuerdo a la posición
					OPEN punkte FOR SELECT DISTINCT(coor),lanes,typeSegment, typePosition, ST_Line_Locate_point(strasse::geometry,coor::geometry) as pos
						FROM "tempPunkte" ORDER BY pos;
					
					/* INIT Encontrar puntos */
					-- por cada punto encontrado
					
					fetch punkte into I1, c1, stype1, type1, pos1;
					-- primer punto encontrado								
					
					LOOP
						fetch punkte into I2, c2, stype2, type2, pos2;
						-- segundo punto encontrado
						RAISE warning '------------------%', S1name;
						IF I2 is NULL THEN 
							--otra rutina
							EXIT;
						ELSE
							-- RAISE warning 'Contador de Intersecciones% ',countInter;
							-- RAISE warning 'POS=>% ,I1:%',pos1,I1;
							-- RAISE warning 'POS=>% ,I2:%',pos2,I2;							
							-- tempM:= createIntersections(c1,c2,S1name,strasseid);
							S2name := NULL;
							I1name := 'I'||(countInter);
							RAISE warning 'pos1:% pos2:%',pos1, pos2;
							RAISE warning 'I1:% I2:%',I1,I2;
							-- verifica que las posición es sean consecutivas que pos1 = n y pos2 = n+1
							IF pos1<pos2 THEN
								auxpos1 := pos1;
								auxpos2 := pos2;
							ELSE 
								auxpos1 := pos2;
								auxpos2 := pos1;
							END IF;
							RAISE warning 'apos1:% apos2:%',auxpos1, auxpos2;
							INSERT INTO "tempMatriz" SELECT I1name, c1, c2, I1, (createIntersections(c1,c2,S1name,strasseid))
								WHERE NOT EXISTS (SELECT coor FROM "tempMatriz" WHERE coor like I1);
							-- VAlida a I1
							IF (type1 like 'init' OR type1 like 'end') THEN
								m1 = createMatrix(c1,c2,'null'::text);
								-- RAISE warning 'EXTREMO %',I1;
								
							ELSE
								SELECT matriz,name,ST_Line_Locate_point(strasse::geometry,coor::geometry) into m1,I1name,auxpos1 
								FROM "tempMatriz" WHERE ST_Equals(coor::geometry,I1::geometry);
							END IF;
							
							S2name:=NULL;
							-- verifica que I2 Ya fueagregado				
							SELECT matriz,name, ST_Line_Locate_point(strasse::geometry,coor::geometry) into m2,I2name,auxpos2 
								FROM "tempMatriz" WHERE ST_Equals(coor::geometry,I2::geometry);
								
							IF I2name is NULL THEN
								auxpos2 := pos2;
								I2name := 'I'||countInter;
								m2 := createIntersections(c1, c2, I2name::text, strasseid);								
								--Crea el segmento S1
								SELECT ST_ASTEXT(ST_LineSubstring(strasse::geometry, auxpos1, auxpos2)) INTO actSegment;
								--por cada uno de los carriles encontrados
								RAISE warning 'p1:% p2:%, calle:%, sub:%',auxpos1, auxpos2, strasse, actSegment;
								
								FOR x IN 1..bahnen LOOP
									-- agrega el segmento. Sí no existe
									
									INSERT INTO segments
										SELECT strasseid,(S1name||'_'||x)::text,'segment','inter', actSegment,
											$1, ST_Length(ST_Transform(ST_SetSRID(actSegment::geometry,900913),26986))
										WHERE NOT EXISTS(
											SELECT coordinates 
											FROM segments 
											WHERE ST_Equals(actSegment::geometry,coordinates::geometry)
											-- OR "segmentName" like (S1name||'_'||x)::text
											);
									
									-- crea la relación
									INSERT INTO "segmentConnections" values($1,(S1name||'_'||x)::text, m2[x][1],'NS');
									INSERT INTO "segmentConnections" values($1,(S1name||'_'||x)::text, m1[x][c2],'NS');
									/* IF S2name IS NOT NULL THEN
										INSERT INTO "segmentConnections" values($1,(S1name||'_'||x)::text, S2name,'NC');
									END IF;*/
									
									RAISE warning '++++++++++++++ I2name No existe';
									RAISE warning 'x:% y:%, cont:%',c1, c2, x;
									RAISE warning 'MATRIZ 1, %',m1[1][x];
									RAISE warning 'MATRIZ 1, %',m1;
									RAISE warning 'MATRIZ 2, %',m2[c2][x];
									RAISE warning 'MATRIZ 2, %',m2;
									RAISe notice '--------------';
									-- crea objeto direccion
									/** INIT direccion**/
									PERFORM createDirection($1, strasseid,m2[x][1], m1[x][c2],direction);
									/** END direccion**/
									S2name := (S1name||'_'||x)::text;
								END LOOP;
								
							ELSE
								FOR x IN 1..bahnen LOOP
								
								SELECT ST_ASTEXT(ST_LineSubstring(strasse::geometry, auxpos1, auxpos2)) INTO actSegment;								
									-- agrega el segmento. Sí no existe
									RAISE warning 'p1:% p2:%, calle:%, sub:%',auxpos1, auxpos2, strasse, actSegment;
									
									INSERT INTO segments
										SELECT strasseid,(S1name||'_'||x)::text,'segment','inter', actSegment,
											$1, ST_Length(ST_Transform(ST_SetSRID(actSegment::geometry,900913),26986))
										WHERE NOT EXISTS(SELECT coordinates FROM segments WHERE ST_Equals(actSegment::geometry,coordinates::geometry));
									-- crea la relación
									INSERT INTO "segmentConnections" values($1,(S1name||'_'||x)::text, m1[1][x],'NS');
									INSERT INTO "segmentConnections" values($1,(S1name||'_'||x)::text, m2[c2][x],'NS');
									/*IF S2name IS NOT NULL THEN
										INSERT INTO "segmentConnections" values($1,(S1name||'_'||x)::text, S2name,'NC');
									END IF;*/
									RAISE warning '++++++++++++++ I2name existe';
									RAISE warning 'x:% y:%, cont:%',c1, c2, x;
									RAISE warning 'MATRIZ 1, %',m1[1][x];
									RAISE warning 'MATRIZ 1, %',m1;
									RAISE warning 'MATRIZ 2, %',m2[c2][x];
									RAISE warning 'MATRIZ 2, %',m2;
									RAISe notice '--------------';
									
									/** INIT direccion**/
									PERFORM createDirection($1, strasseid, m1[1][x], m2[c2][x],direction);
									/** END direccion**/
									S2name := (S1name||'_'||x)::text;
									
								END LOOP;
							END IF;
							
						END IF;
						countInter := countInter +1;
						S2name := S1name;
						pos1 := pos2;
						I1 := I2;				
					END LOOP;/* END Encontrar puntos */			 
					CLOSE punkte;				
				END IF;
				
			END LOOP;
			
		END IF;
		
		return QUERY 
			SELECT s."segmentName" as "name",
				ST_AsText(St_transform(St_setSRID(coordinates::geometry,900913),4326)) as lonlat,
				coordinates, "distance" as "length", s."segmentType","type" FROM segments s, "segmentConnections" sc 
				WHERE "type" like 'inter' AND s."segmentName" like 'I%' AND s."idPropuesta" = $1 
				AND sc."segmentConnection" = s."segmentName" AND sc."segmentConnection" not like '%S%'
			union SELECT "segmentName" as "name",
				ST_AsText(St_transform(St_setSRID(coordinates::geometry,900913),4326)) as lonlat,
				coordinates,"distance" as "length", "segmentType","type" from segments
				WHERE "segmentName" like 'S%' AND "idPropuesta" = $1
			
			ORDER BY "name"
			;

	END;
	$BODY$
	  LANGUAGE plpgsql VOLATILE;
	