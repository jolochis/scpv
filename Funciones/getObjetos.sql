CREATE OR REPLACE FUNCTION "getObjetos"(IN puntos text, IN p integer)
  RETURNS TABLE(_name text, _type text, _points text, _sid text, _s1 text, _s2 text, _s3 text, _cid text, _init text, _end text, _attr text) AS
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
	o.id,o.type,ST_AsText(o.positionscr),ST_AsText(o.positiondst),
	e.idconfig,e.attr1,e.attr2,e.attr3,
	c.id,c.init,c.tend,c.attr
FROM objetos o 
	join configuracion c on c.idobject = o.id
	join propuestas p on p.id = o.idprop
	left join estado e on e.idconfig = c.id
WHERE o.idprop = $2 AND
	ST_intersects(ST_Buffer($1::geometry,4),ST_AsText(o.positionscr)::geometry)
AND (o.type like '%aforo%' OR o.type like '%tope%' OR o.type like 'direction');

LOOP
FETCH objects INTO auxid,auxtype,auxposscr,auxposdst,
idstate,attr1,attr2,attr3,
idconfig,init,tend,attr
;

IF(auxid IS NULL) 
THEN
exit;
ELSE
IF(auxposscr NOT LIKE '%LINE%') 
THEN
inicio = auxposscr;
ELSE
SELECT ST_AsText(ST_StartPoint(ST_lineMerge(auxposscr::geometry))) into inicio;
END IF;

IF(SELECT ST_Intersects(inicio::geometry,ST_Buffer($1::geometry,4))) THEN

IF(char_length(cast(init as text))=0) THEN
init='-1';
END IF;

RETURN QUERY SELECT auxid,auxtype,
ST_AsText(St_transform(St_setSRID(auxposscr::geometry,900913),4326)),
idstate,attr1,attr2,attr3,
idconfig,init,tend,attr;
END IF;

END IF;

END LOOP;
CLOSE objects;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION "getObjetos"(text, integer)
  OWNER TO postgres;
