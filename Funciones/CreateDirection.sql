-- Function: createdirection(integer, integer, text, text)

DROP FUNCTION createdirection(integer, integer, text, text);

CREATE OR REPLACE FUNCTION createdirection(prop integer, strasseid integer, i1 text, i2 text,dir integer)
  RETURNS integer AS
$BODY$
	declare	
	auxid INTEGER;
	begin
		IF dir = -1 THEN 
			INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
					VALUES($1, 'direction', 'POINT(0 0)'::geometry, 
					strasseid, 'POINT(0 0)'::geometry, strasseid, $3::text)
					RETURNING id INTO auxid;
			INSERT INTO configuracion(idobject,init,tend,attr)
					VALUES(auxid, 0, '$', I1);
			
			INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
					VALUES($1, 'direction', 'POINT(0 0)'::geometry, 
					strasseid, 'POINT(0 0)'::geometry, strasseid, $4::text) 
					RETURNING id INTO auxid;
			INSERT INTO configuracion(idobject,init,tend,attr)
					VALUES(auxid, 0, '$', I2);
		ELSE IF dir = 1 THEN
				INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
					VALUES($1, 'direction', 'POINT(0 0)'::geometry, 
					strasseid, 'POINT(0 0)'::geometry, strasseid, $4::text)
					RETURNING id INTO auxid;
				INSERT INTO configuracion(idobject,init,tend,attr)
					VALUES(auxid, 0, '$', I2);
			ELSE
				INSERT INTO objetos (idprop, "type", positionscr, linescr, positiondst, linedst, segment)
					VALUES($1, 'direction', 'POINT(0 0)'::geometry, 
					strasseid, 'POINT(0 0)'::geometry, strasseid, $3::text)       
					RETURNING id INTO auxid;
				INSERT INTO configuracion(idobject,init,tend,attr)
					VALUES(auxid, 0, '$', I1);
			END IF;
		END IF;
		return 1;
	END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION createdirection(integer, integer, text, text)
  OWNER TO postgres;
