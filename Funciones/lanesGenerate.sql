CREATE OR REPLACE FUNCTION "lanesGenerate"(IN Sname,IN c1 integer, IN c2 integer, in lanes integer)
	  RETURNS INTEGER AS
	$BODY$DECLARE
		I2name text;
		I1name text;
		m1 text[][];
		
		I1 text;
		I2 text;
		
		pos1 float;
		pos2 float;
		auxpos1 float;
		auxpos2 float;
	
	BEGIN
		S2name := NULL;							
		auxpos1 := pos1;
		auxpos2 := pos2;
		
		INSERT INTO "tempMatriz" SELECT $1, 0.0, $2, $3,I1, (createIntersections($2,$3,$1,strasseid))
			WHERE NOT EXISTS (SELECT coor FROM "tempMatriz" WHERE coor like I1);
		-- VAlida a I1
		IF (type1 like 'init' OR type1 like 'end') THEN
			m1 = createMatrix($2,$3,'null'::text);
			I1name := 'I'||(countInter-1);
		ELSE
			SELECT matriz,name,pos into m1,I1name,auxpos1 FROM "tempMatriz" WHERE ST_Equals(coor::geometry,I1::geometry);
		END IF;
	
		-- verifica que I2 Ya fueagregado				
		SELECT matriz,name, pos into m2,I2name,auxpos2 FROM "tempMatriz" WHERE ST_Equals(coor::geometry,I2::geometry);
		IF I2name is NULL THEN
			I2name := 'I'||countInter;
			m2 := createMatrix($2, $3, I2name::text);
			countInter = countInter + 1;
			--Crea el segmento S1
			SELECT ST_ASTEXT(ST_LineSubstring(strasse::geometry, auxpos1, auxpos2)) INTO actSegment;
			--por cada uno de los carriles encontrados
			
			FOR x IN 1..$4 LOOP
				-- agrega el segmento. Sí no existe
				RAISE NOTICE 'Ciclo%',x;
				INSERT INTO segments
					SELECT strasseid,($1||'_-'||x)::text,'segment','inter', actSegment,
						$1, ST_Length(ST_Transform(ST_SetSRID(actSegment::geometry,900913),26986))
					WHERE NOT EXISTS(SELECT coordinates FROM segments WHERE ST_Equals(actSegment::geometry,coordinates::geometry));
				-- crea la relación
				INSERT INTO "segmentConnections" values($1,($1||'_'||x)::text, m2[x][1],'NS');
				INSERT INTO "segmentConnections" values($1,($1||'_'||x)::text, m1[x][$3],'NS');
				INSERT INTO "segmentConnections" SELECT $1,($1||'_'||x)::text, S2name,'NC'
				WHERE EXISTS(SELECT S2name);
				-- crea objeto direccion
				/** INIT direccion**/
				PERFORM createDirection($1, strasseid,m2[x][1], m1[x][$3],direction);
				/** END direccion**/
				S2name := ($1||'_'||x)::text;
			END LOOP;
			
		ELSE
			FOR x IN 1..$4 LOOP
			RAISE NOTICE 'Ciclo%',x;
			SELECT ST_ASTEXT(ST_LineSubstring(strasse::geometry, auxpos1, auxpos2)) INTO actSegment;
				-- agrega el segmento. Sí no existe
				INSERT INTO segments
					SELECT strasseid,($1||'_'||x)::text,'segment','inter', actSegment,
						$1, ST_Length(ST_Transform(ST_SetSRID(actSegment::geometry,900913),26986))
					WHERE NOT EXISTS(SELECT coordinates FROM segments WHERE ST_Equals(actSegment::geometry,coordinates::geometry));
				-- crea la relación
				INSERT INTO "segmentConnections" values($1,($1||'_'||x)::text, m1[1][x],'NS');
				INSERT INTO "segmentConnections" values($1,($1||'_'||x)::text, m1[$3][x],'NS');
				INSERT INTO "segmentConnections" SELECT $1,($1||'_'||x)::text, S2name,'NC' 
				WHERE EXISTS(SELECT S2name);
				/** INIT direccion**/
				PERFORM createDirection($1, strasseid, m1[1][x], m1[$3][x],direction);
				/** END direccion**/
				S2name := ($1||'_'||x)::text;
				
			END LOOP;
		END IF;
END;
	$BODY$
	  LANGUAGE plpgsql VOLATILE;