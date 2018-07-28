	drop function createMatrix(integer, integer,text);
	/**
	* Se encarga de generar la matriz de intersecciones entre dos calles
	* @param {integer} n carriles de la calle actual
	* @param {interger} m carrilles de la calle intersectada
	* @param {text} name nombre de la intersección actual
	*
	* @return {text[][]} matriz una matriz con los nombres de las interseciones de la intersección
	*/
	create function createMatrix(IN n integer, IN m integer,IN content text)
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
				inp:= inp || $3;
				if (y +1)<=$2 THEN inp:= inp||'_'||x||','; ENd if;
			END LOOP;
			
			matriz:=matriz||ARRAY[string_to_array(inp,',')];
		END LOOP;
		
		-- crea las relaciones de las intersecciones		
		return matriz;
	END;
	$body$
	LANGUAGE plpgsql VOLATILE;