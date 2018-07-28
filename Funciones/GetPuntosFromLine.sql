-- Function: "getPuntos"(text)

-- DROP FUNCTION "getPuntos"(text);

CREATE OR REPLACE FUNCTION "getPuntos"(IN line1 text)
  RETURNS TABLE(angle double precision, punto text) AS
$BODY$declare 
  p1 refcursor;
  p2 refcursor;
  punto1 text;
  punto2 text;
  subPoint1 text;
  subPoint2 text;
  subLinea text;
  inverso  int default 0;
  nPuntos int;
  x int default 1;
  ang float;
  

begin

  truncate tempPuntos;

  Select st_NPoints(st_geomfromtext($1)) into nPuntos;
  LOOP
	if(x>=nPuntos) then
		
		exit;
	else
		
		select ST_PointN(ST_GeomFromText($1),x) into subPoint1;
		select ST_PointN(ST_GeomFromText($1),x+1) into subPoint2;
		
		SELECT 
			(Case (t.pnt1>t.pnt2)
			when 't' then (Select ST_AsText(ST_Line_Substring(ST_GeomFromText($1),t.pnt2,t.pnt1)))
				else (Select ST_AsText(ST_Line_Substring(ST_GeomFromText($1),t.pnt1,t.pnt2)))
			END) ,
			(case (t.pnt1<t.pnt2)
				when 't' then 1
				else 0 end) into subLinea,inverso
		from 
			(Select ST_Line_Locate_Point(ST_GeomFromText($1),ST_GeomFromText(subPoint1)) as pnt1,
			ST_Line_Locate_Point(ST_GeomFromText($1),ST_GeomFromText(subPoint2)) as pnt2)as t;
		x=x+1;
  
		OPEN p1 FOR SELECT st_asText(ST_Line_Interpolate_Point(m.l,generate_series(1,cast(m.d as Integer),20)/m.d))
		  FROM (SELECT ST_distance(st_GeomFromText(subPoint1),st_GeomFromText(subPoint2)) as d,st_GeomFromText(subLinea) as l ) as m limit 100;

		OPEN p2 FOR SELECT st_asText(ST_Line_Interpolate_Point(m.l,generate_series(5,cast(m.d as Integer),20)/m.d) )
		  FROM (SELECT ST_distance(st_GeomFromText(subPoint1),st_GeomFromText(subPoint2)) as d,st_GeomFromText(subLinea) as l ) as m limit 100;
		IF(inverso = 0) then
			select degrees(ST_Azimuth(st_geomfromtext(subPoint1),st_geomfromtext(subPoint2))) into ang;
		else
			select degrees(ST_Azimuth(st_geomfromtext(subPoint2),st_geomfromtext(subPoint1))) into ang;
		end if;
	  LOOP
	
		--if(ang>90 and ang<270) then
			fetch p1 into punto1;
			fetch p2 into punto2;
		--else
		--	fetch p1 into punto2;
		--	fetch p2 into punto1;
		--END IF;
		IF punto2 ISNULL OR punto1 ISNULL then
		exit;
		else
	
		INSERT into tempPuntos values (degrees(ST_Azimuth(st_geomfromtext(punto1),st_geomfromtext(punto2))), ST_AsGEOJSON(punto1));
		--INSERT into tempPuntos values (ang, ST_AsGEOJSON(punto1));
		END IF;
     
	  END LOOP;
	  CLOSE p1;
	  CLOSE p2;
	END IF;
  END LOOP;
  return QUERY Select * from tempPuntos;
end;$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 100;
ALTER FUNCTION "getPuntos"(text)
  OWNER TO postgres;
