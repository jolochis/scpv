CREATE OR REPLACE FUNCTION "getPuntosSubLinea"(IN line1 text)
  RETURNS TABLE(angle double precision, punto text) AS
$BODY$declare 
  p1 refcursor;
  p2 refcursor;
  punto1 text;
  punto2 text;
  subPoint1 text;
  subPoint2 text;
  subLinea text;
  mainLine geometry;
  linea text;
  ang float;
  nPuntos int;
  x int default 1;
  inverso smallint default 0;
  

begin

  truncate tempPuntos;
  
  select $1::geometry into mainLine
  
  Select 
	(Case (t.pnt1>t.pnt2)
		when 't' then (Select ST_AsText(ST_Line_Substring($3::geometry,t.pnt2,t.pnt1)))
			else (Select ST_AsText(ST_Line_Substring($3::geometry,t.pnt1,t.pnt2)))
		END),
	(case (t.pnt1<t.pnt2)
		when 't' then 1
		else 0 end) into linea,inverso
	from 
		(Select ST_Line_Locate_Point($3::geometry,$1::geometry) as pnt1,
		ST_Line_Locate_Point($3::geometry,$2::geometry) as pnt2)as t;
  
  Select st_NPoints(linea::geometry) into nPuntos;
  LOOP
	if(x>=nPuntos) then
		
		exit;
	else
		if(inverso = 0) then
			select ST_PointN(linea::geometry,x) into subPoint1;
			select ST_PointN(linea::geometry,x+1) into subPoint2;
		else
			select ST_PointN(linea::geometry,x) into subPoint2;
			select ST_PointN(linea::geometry,x+1) into subPoint1;
		end if;
		
		SELECT 
			Case (t.pnt1>t.pnt2)
			when 't' then (Select ST_AsText(ST_Line_Substring(linea::geometry,t.pnt2,t.pnt1)))
				else (Select ST_AsText(ST_Line_Substring(linea::geometry,t.pnt1,t.pnt2)))
			END into subLinea
		from 
			(Select ST_Line_Locate_Point(linea::geometry,subPoint1::geometry) as pnt1,
			ST_Line_Locate_Point(linea::geometry,subPoint2::geometry) as pnt2)as t;
		x=x+1;
  
	  OPEN p1 FOR SELECT st_asText(ST_Line_Interpolate_Point(m.l,generate_series(1,cast(m.d as Integer),20)/m.d))
		  FROM (SELECT ST_distance(subPoint1::geometry,subPoint2::geometry) as d,subLinea::geometry as l ) as m limit 100;

	  OPEN p2 FOR SELECT st_asText(ST_Line_Interpolate_Point(m.l,generate_series(5,cast(m.d as Integer),20)/m.d) )
		  FROM (SELECT ST_distance(subPoint1::geometry,subPoint2::geometry) as d,subLinea::geometry as l ) as m limit 100;

	  select degrees(ST_Azimuth(subPoint1::geometry,subPoint2::geometry)) into ang;
	  LOOP
	
		fetch p1 into punto1;
		fetch p2 into punto2;
		
		IF punto2 ISNULL OR punto1 ISNULL then
			exit;
		else
	
		INSERT into tempPuntos values (degrees(ST_Azimuth(punto1::geometry,punto2::geometry)), ST_AsGEOJSON(punto1));
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
ALTER FUNCTION "getPuntosSubLinea"(text, text, text)
  OWNER TO postgres;