-- Function: "getObjetos"(integer, integer)

-- DROP FUNCTION "getObjetos"(integer, integer);

CREATE OR REPLACE FUNCTION "getObjetos"(IN propuesta integer, IN _user integer)
  RETURNS TABLE(_id text, _idu integer, _idp integer, _type text, _coor text, _sid text, _s1 text, _s2 text, _s3 text, _cid text, _init text, _end text, _attr text) AS
$BODY$declare
	objects refcursor;
	auxid text;
	auxposscr text;
	inicio text;
	auxtype text;
	auxposdst text;
	auxsegment text;
	init text;
	tend text;
	idstate text;
	idconfig text;
	attr text;
	attr1 text;
	attr2 text;
	attr3 text;
	punto text;
	
BEGIN
	
	OPEN objects FOR select 
		distinct(o.id),o.type,ST_AsGeoJSON(ST_asText(o.positionscr)),
		e.idconfig,e.attr1,e.attr2,e.attr3,
		c.id,c.init,c.tend,c.attr
	FROM objetos o 
	join configuracion c on c.idobject = o.id
	join propuestas p on p.id = o.idprop
	left join estado e on e.idconfig = c.id
	WHERE (o.idprop = $1 AND p.iduser = $2)
		AND (o.type like '%aforo%' OR o.type like '%tope%' OR o.type like '%direction%');
	
		LOOP
			FETCH objects INTO auxid,auxtype,auxposscr,
			idstate,attr1,attr2,attr3,
			idconfig,init,tend,attr
			;
			
			IF(auxid IS NULL) THEN
				exit;
			ELSE
				IF(auxposscr NOT LIKE '%LINE%') THEN
					RETURN QUERY SELECT auxid,$2,$1,auxtype,auxposscr as _coor,
						idstate,attr1,attr2,attr3,
						idconfig,init,tend,attr;
				ELSE
					RETURN QUERY 
						SELECT coordinates as _coor
						FROM "getLinePoints"(ST_StartPoint(auxposscr::geometry),ST_EndPoint(auxposscr::geometry))
						CROSS JOIN (SELECT auxid,$2,$1,auxtype,
						idstate,attr1,attr2,attr3,
						idconfig,init,tend,attr) as t;
				END IF;
				
			END IF;
		
		END LOOP;
		CLOSE objects;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION "getObjetos"(integer, integer)
  OWNER TO postgres;
