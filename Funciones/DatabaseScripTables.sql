
-- Table: usuario

-- DROP TABLE usuario;
CREATE SEQUENCE au_user
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE au_user
  OWNER TO postgres;

CREATE TABLE usuario
(
  id integer NOT NULL DEFAULT nextval('au_user'::regclass),
  benutzername text NOT NULL,
  name text NOT NULL,
  email text,
  pass character varying,
  CONSTRAINT pk_user PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE usuario
  OWNER TO postgres;
-- Sequence: au_call

-- DROP SEQUENCE au_call;

CREATE SEQUENCE au_call
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE au_call
  OWNER TO postgres;

  
  -- Sequence: au_call

-- DROP SEQUENCE au_call;

CREATE SEQUENCE au_conf
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE au_conf
  OWNER TO postgres;


-- Table: configuracion

-- DROP TABLE configuracion;

CREATE TABLE configuracion
(
  id integer NOT NULL DEFAULT nextval('au_conf'::regclass),
  idobject integer,
  init character varying(255),
  tend character varying(255),
  attr text,
  CONSTRAINT pk_configuration PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE configuracion
  OWNER TO postgres;
  
  

-- Table: configuracion_simulacion

-- DROP TABLE configuracion_simulacion;

CREATE SEQUENCE au_csim
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE au_csim
  OWNER TO postgres;

CREATE TABLE configuracion_simulacion
(
  id bigint NOT NULL DEFAULT nextval('au_csim'::regclass),
  id_usuario bigint NOT NULL,
  puntos_propuesta bigint,
  url_utyil text,
  id_propuesta bigint,
  status integer DEFAULT 0,
  entrgado integer DEFAULT 0,
  CONSTRAINT llave PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE configuracion_simulacion
  OWNER TO postgres;

  
-- Table: estado

-- DROP TABLE estado;

CREATE TABLE estado
(
  idconfig integer,
  attr1 text,
  attr2 text,
  attr3 text,
  CONSTRAINT estado_idconfig_fkey FOREIGN KEY (idconfig)
      REFERENCES configuracion (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE estado
  OWNER TO postgres;

  
-- Table: objetos

-- DROP TABLE objetos;

CREATE SEQUENCE au_obj
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE au_obj
  OWNER TO postgres;

CREATE TABLE objetos
(
  id integer NOT NULL DEFAULT nextval('au_obj'::regclass),
  type text,
  idprop integer,
  positionscr geometry,
  linescr integer,
  positiondst geometry,
  linedst integer,
  segment text
)
WITH (
  OIDS=FALSE
);
ALTER TABLE objetos
  OWNER TO postgres;

  
-- Table: propuestas

-- DROP TABLE propuestas;
CREATE SEQUENCE au_prop
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE au_prop
  OWNER TO postgres;

CREATE TABLE propuestas
(
  id integer NOT NULL DEFAULT nextval('au_prop'::regclass),
  iduser integer,
  date_hour date,
  minx character varying,
  maxy character varying,
  miny character varying,
  maxx character varying,
  nombre character varying,
  CONSTRAINT pk_proposals PRIMARY KEY (id),
  CONSTRAINT fk_user FOREIGN KEY (iduser)
      REFERENCES usuario (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE propuestas
  OWNER TO postgres;

  
-- Table: "segmentConnections"

-- DROP TABLE "segmentConnections";

CREATE TABLE "segmentConnections"
(
  "idPropuesta" integer,
  "segmentName" character varying(30),
  "segmentConnection" character varying(30),
  "connectionType" character varying(3)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "segmentConnections"
  OWNER TO postgres;

  
-- Table: segments

-- DROP TABLE segments;

CREATE TABLE segments
(
  "idCalle" integer,
  "segmentName" character varying(10),
  "segmentType" character varying(30),
  type character varying(30),
  coordinates text,
  "idPropuesta" integer,
  distance double precision
)
WITH (
  OIDS=FALSE
);
ALTER TABLE segments
  OWNER TO postgres;

  
-- Table: "simulationResults"

-- DROP TABLE "simulationResults";

CREATE TABLE "simulationResults"
(
  "idPropuesta" integer,
  "segmentName" text,
  "idEvent" integer,
  type text,
  "idObject" text,
  "time" double precision,
  "position" double precision
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "simulationResults"
  OWNER TO postgres;

  
-- Table: temp_intersections

-- DROP TABLE temp_intersections;

CREATE TABLE temp_intersections
(
  idline1 integer NOT NULL,
  idline2 integer NOT NULL,
  type text,
  interpunto text NOT NULL,
  "to" text,
  "from" text,
  name text,
  from2 text,
  to2 text,
  distance double precision
)
WITH (
  OIDS=FALSE
);
ALTER TABLE temp_intersections
  OWNER TO postgres;

  
-- Table: temp_segmentos

-- DROP TABLE temp_segmentos;

CREATE TABLE temp_segmentos
(
  idsegmento text NOT NULL,
  "to" text,
  "from" text,
  coordenadas text,
  longitud numeric,
  type text DEFAULT 'inter'::text,
  from2 text,
  to2 text
)
WITH (
  OIDS=FALSE
);
ALTER TABLE temp_segmentos
  OWNER TO postgres;

  
-- Table: temppuntos

-- DROP TABLE temppuntos;

CREATE TABLE temppuntos
(
  angle double precision,
  punto text
)
WITH (
  OIDS=FALSE
);
ALTER TABLE temppuntos
  OWNER TO postgres;
  
