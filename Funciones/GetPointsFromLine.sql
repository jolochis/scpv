create or replace function GetPointsFromLine(IN line text)
returns Table(angle double precision, punto text) as
$BODY$
	DECLARE
		l geometry;
	begin
		select line::geometry into l;
		
		--INICIO generación de puntos e inclinación
		return QUERY
			SELECT 
				Degrees(ST_Azimuth(ST_Line_Interpolate_Point(l,CAST(n as double precision)/10), ST_Line_Interpolate_Point(l,(CAST(n+1 as double precision))/10))) as angle,--Inclinación
				ST_AsGEOJSON(sT_AsText(ST_Line_Interpolate_Point(l,CAST(n as double precision)/10)))--punto en json
			FROM generate_series(1,9) as n;
		--FIN Generación de puntos
	
	end;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 100;
ALTER FUNCTION GetPointsFromLine(text)
  OWNER TO postgres;