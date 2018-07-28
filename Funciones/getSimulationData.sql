-- Function: "getSimulationData"(text, integer, integer, integer)

-- DROP FUNCTION "getSimulationData"(text, integer, integer, integer);

CREATE OR REPLACE FUNCTION "getSimulationData"(IN segmento text, IN propuesta integer, IN tinit integer, IN tend integer)
  RETURNS TABLE(flujo double precision, densidad double precision) AS
$BODY$declare
		flow double precision;
		auxflow double precision;
		density double precision;
	BEGIN
		SELECT count("type") FROM "simulationResults"
			WHERE "type" like 'LL' 
			AND "segmentName" like $1 AND "idPropuesta" = $2
			AND "time" BETWEEN tinit AND tend
			INTO flow;
			
			
		SELECT count("type") FROM "simulationResults"
			WHERE "type" like 'LL' 
			AND "segmentName" like $1 AND "idPropuesta" = $2
			AND "time" BETWEEN 0 AND tend
			INTO auxflow;
		
			
		SELECT count("type") FROM "simulationResults"
			WHERE ("type" like 'AE' OR "type" like 'ALE')
			AND "segmentName" like $1 AND "idPropuesta" = $2
			AND "time" BETWEEN 0 AND tend
			INTO density;
			
		RETURN QUERY 
			SELECT flow,density-auxflow;
	END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION "getSimulationData"(text, integer, integer, integer)
  OWNER TO postgres;
