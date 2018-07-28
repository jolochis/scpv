	drop function createIntersections(integer, integer,text,integer);
	/**
	* Se encarga de generar la matriz de intersecciones entre dos calles
	* @param {integer} n carriles de la calle actual
	* @param {interger} m carrilles de la calle intersectada
	* @param {text} name nombre de la intersección actual
	*
	* @return {text[][]} matriz una matriz con los nombres de las interseciones de la intersección
	*/
	create function createIntersections(IN n integer, IN m integer,IN name text, IN propuesta integer)
	RETURNS text[][]
	as
	$body$declare
	matriz text[][];
	auxm text[][];
	cont integer;
	x integer;
	y integer;
	inp text;

	begin
		cont :=0;
		-- crea la matriz de intersecciones
		for x in 1..$1 LOOP
			inp := '';
			for y in 1..$2 LOOP
				cont:= cont+1;
				inp:= inp || (name||'_'||cont::text);
				if (y +1)<=$2 THEN inp:= inp||','; ENd if;
			END LOOP;
			
			matriz:=matriz||ARRAY[string_to_array(inp,',')];
		END LOOP;
		
		-- crea las relaciones de las intersecciones
		auxm:=matriz;
		for x in 1..$1 LOOP			
			for y in 1..$2 LOOP
				IF auxm[x][y] IS NOT NULL THEN
					IF auxm[x-1][y] IS NOT NULL THEN
						INSERT INTO "segmentConnections"("idPropuesta","segmentName","segmentConnection","connectionType") 
							values($4, auxm[x][y], auxm[x-1][y], 'NC');						
					END IF;
					
					IF auxm[x+1][y] IS NOT NULL THEN
						INSERT INTO "segmentConnections"("idPropuesta","segmentName","segmentConnection","connectionType") 
							values($4,auxm[x][y],auxm[x+1][y],'NC');						
					END IF;
					
					IF auxm[x][y-1] IS NOT NULL THEN
						INSERT INTO "segmentConnections"("idPropuesta","segmentName","segmentConnection","connectionType") 
							values($4,auxm[x][y],auxm[x][y-1],'NC');						
					END IF;
					
					IF auxm[x][y+1] IS NOT NULL THEN
						INSERT INTO "segmentConnections"("idPropuesta","segmentName","segmentConnection","connectionType") 
							values($4,auxm[x][y],auxm[x][y+1],'NC');						
					END IF;
					auxm[x][y] = NULL;
				END IF;
			END LOOP;			
		END LOOP;
		
		return matriz;
	END;
	$body$
	LANGUAGE plpgsql VOLATILE;