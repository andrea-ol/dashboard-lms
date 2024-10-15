-------------------ESTE SCRIPT SE HA CREADO PARA LLEVAR CONTROL DE LAS FUNCIONES SQL QUE DEBEN SER EJECUTADAS DENTRO DE LA BASE DE DATOS ZAJUNA
-- PARA SU FUNCIONAMIENTO DENTRO DEL CENTRO DE RESULTADOS

-------------------CREACION DE SERVIDOR REMOTO PARA EXPORTAR TABLAS DE INTEGRACION--------------------
-- En ambas bases de datos
DROP EXTENSION IF EXISTS postgres_fdw CASCADE;
CREATE EXTENSION postgres_fdw;

-- Crear el servidor remoto para la DB integracion_replica, se instala en la base de datos de ZAJUNA
CREATE SERVER integracion_server
FOREIGN DATA WRAPPER postgres_fdw
OPTIONS (host '127.0.0.1', dbname 'integracion_replica-v3', port '5432');

-- Crear el mapeo de usuario
CREATE USER MAPPING FOR CURRENT_USER
SERVER integracion_server
OPTIONS (user 'postgres', password '12345');

--
IMPORT FOREIGN SCHEMA "INTEGRACION"
LIMIT TO ("RESULTADO_APRENDIZAJE","COMPETENCIA")
FROM SERVER integracion_server
INTO public;

-- Importar todas las tablas del esquema "RESULTADOS"
IMPORT FOREIGN SCHEMA "RESULTADOS"
FROM SERVER  integracion_server
INTO public;

----------------------FUNCION PARA OBTENER LA SESSION DEL USUARIO LOGUEADO----------------

CREATE OR REPLACE FUNCTION obtenerSessionRA(curso VARCHAR, iduser BIGINT, tipo BIGINT)
RETURNS VOID AS
$$
begin
	RAISE NOTICE 'Inicio de la función';
    -- Crear la tabla temporal
    CREATE TEMPORARY TABLE IF NOT EXISTS tablaTempSes(
        userid BIGINT,
        username VARCHAR,
        firstname VARCHAR,
        lastname VARCHAR,
        email VARCHAR,
        shortname VARCHAR,
        tipo_user BIGINT,
        courseid BIGINT
    );

    -- Insertar los datos en la tabla temporal
    INSERT INTO tablaTempSes(userid, username, firstname, lastname, email, shortname, tipo_user, courseid)
    
    SELECT  
    u.id as userid, 
    u.username, 
    u.firstname, 
    u.lastname, 
    u.email, 
    r.shortname, 
    ra.roleid as tipo_user,
    e.courseid
    
    FROM mdl_user u
    JOIN mdl_user_enrolments ue ON ue.userid = u.id
    JOIN mdl_enrol e ON e.id = ue.enrolid
    JOIN mdl_role_assignments ra ON ra.userid = u.id
    JOIN mdl_course mc ON mc.id = e.courseid
    JOIN mdl_role r ON r.id = ra.roleid
    WHERE u.id = iduser AND mc.idnumber = curso AND ra.roleid = tipo;

    -- Crear la vista a partir de la tabla temporal
    CREATE OR REPLACE VIEW vista_ses AS 
    SELECT * FROM tablaTempSes;
   
END;
$$
LANGUAGE 'plpgsql';

----------------------------------------------------------------------------

------------------FUNCION PARA OBTENER RESULTADOS DE APRENDIZAJE CON USUARIOS DE ZAJUNA-----------------------
/* 
CREATE OR REPLACE FUNCTION obtenerResultados(curso VARCHAR, competencia VARCHAR, tabla VARCHAR)
RETURNS VOID AS
$$
begin
RAISE NOTICE 'Inicio de la función';
-- Crear la tabla temporal
CREATE TEMPORARY TABLE IF NOT EXISTS tablaTemp(
"ID_USUARIO_LMS" BIGINT,
"USR_NUM_DOC" VARCHAR,
"USR_NOMBRE" VARCHAR,
"USR_APELLIDO" VARCHAR,
"USR_EMAIL" VARCHAR,
"FIC_ID" VARCHAR,
"ESTADO" BIGINT,
"CMP_ID" BIGINT,
"CMP_NOMBRE" VARCHAR,
"REA_ID" BIGINT,
"REA_NOMBRE" VARCHAR,
"ADR_ID" BIGINT,
"ADR_EVALUACION_RESULTADO" VARCHAR,
"ESTADO_SINCRONIZACION" BIGINT,
"usr_tipo_doc" VARCHAR
);

-- Insertar los datos en la tabla temporal
INSERT INTO tablaTemp("ID_USUARIO_LMS", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO","USR_EMAIL","FIC_ID", "ESTADO", "CMP_ID", "CMP_NOMBRE", "REA_ID", "REA_NOMBRE", "ADR_ID", "ADR_EVALUACION_RESULTADO", "ESTADO_SINCRONIZACION","usr_tipo_doc")
SELECT
u.id AS "ID_USUARIO_LMS",
u.username AS "USR_NUM_DOC",
u.firstname AS "USR_NOMBRE",
u.lastname AS "USR_APELLIDO",
u.email AS "USR_EMAIL",
mc.idnumber AS "FIC_ID",
ue.status as "ESTADO",
adr."CMP_ID",
c."CMP_NOMBRE",
rap."REA_ID",
rap."REA_NOMBRE",
adr."ADR_ID",
adr."ADR_EVALUACION_RESULTADO",
adr."ESTADO_SINCRONIZACION",
adr."usr_tipo_doc"
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_course mc ON mc.id = e.courseid
JOIN mdl_role r ON r.id = ra.roleid
JOIN public."TABLA_NOTASXRA_CC" adr ON u.id = adr."LMS_ID"
JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
join public."COMPETENCIA" c on c."CMP_ID" = adr."CMP_ID"
WHERE mc.idnumber = curso AND adr."CMP_ID" = competencia::BIGINT
ORDER BY u.firstname ASC;

-- Crear la vista a partir de la tabla temporal
CREATE OR REPLACE VIEW vista_result AS
SELECT * FROM tablaTemp;

END;
$$
LANGUAGE 'plpgsql'; */

---------------------FUNCION PARA OBTENER LOS RESULTADOSAP POR APRENDIZ CON USUARIO DE ZAJUNA-------------------

/* CREATE OR REPLACE FUNCTION obtenerResultadosAprendiz(curso VARCHAR, competencia VARCHAR, user_id BIGINT)
RETURNS VOID AS
$$
begin
RAISE NOTICE 'Inicio de la función';
-- Crear la tabla temporal
CREATE TEMPORARY TABLE IF NOT EXISTS tablaTempApr(
"LMS_ID" BIGINT,
"USR_NUM_DOC" VARCHAR,
"USR_NOMBRE" VARCHAR,
"USR_APELLIDO" VARCHAR,
"USR_EMAIL" VARCHAR,
"FIC_ID" VARCHAR,
"CMP_ID" BIGINT,
"REA_ID" BIGINT,
"REA_NOMBRE" VARCHAR,
"ADR_ID" BIGINT,
"ADR_EVALUACION_RESULTADO" VARCHAR,
"ESTADO_SINCRONIZACION" BIGINT
);

-- Insertar los datos en la tabla temporal
INSERT INTO tablaTempApr("LMS_ID", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO","USR_EMAIL","FIC_ID", "CMP_ID", "REA_ID", "REA_NOMBRE", "ADR_ID", "ADR_EVALUACION_RESULTADO", "ESTADO_SINCRONIZACION")
select DISTINCT
u.id AS LMS_ID,
u.username AS "USR_NUM_DOC",
u.firstname AS "USR_NOMBRE",
u.lastname AS "USR_APELLIDO",
u.email AS "USR_EMAIL",
mc.idnumber AS "FIC_ID",
adr."CMP_ID",
rap."REA_ID",
rap."REA_NOMBRE",
adr."ADR_ID",
adr."ADR_EVALUACION_RESULTADO",
adr."ESTADO_SINCRONIZACION"
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_course mc ON mc.id = e.courseid
JOIN mdl_role r ON r.id = ra.roleid
JOIN public."TABLA_NOTASXRA_CC" adr ON u.id = adr."LMS_ID"
JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
WHERE mc.idnumber = curso AND adr."CMP_ID" = competencia::BIGINT AND u.id = user_id
ORDER BY u.firstname ASC;

-- Crear la vista a partir de la tabla temporal
CREATE OR REPLACE VIEW vista_resultap AS
SELECT * FROM tablaTempApr;

END;
$$
LANGUAGE 'plpgsql'; */

-------------------FUNCION PARA OBTENER RESULTADOS POR RESULTADO DE APRENDIZAJE CON USUARIOS DE ZAJUNA--------------------
/* 
CREATE OR REPLACE FUNCTION obtenerResultadosRea(curso VARCHAR, competencia VARCHAR, rea_id BIGINT)
RETURNS VOID AS
$$
begin
RAISE NOTICE 'Inicio de la función';
-- Crear la tabla temporal
CREATE TEMPORARY TABLE IF NOT EXISTS tablaTempApr(
"LMS_ID" BIGINT,
"USR_NUM_DOC" VARCHAR,
"USR_NOMBRE" VARCHAR,
"USR_APELLIDO" VARCHAR,
"USR_EMAIL" VARCHAR,
"FIC_ID" VARCHAR,
"ESTADO" BIGINT, 
"CMP_ID" BIGINT,
"REA_ID" BIGINT,
"REA_NOMBRE" VARCHAR,
"ADR_ID" BIGINT,
"ADR_EVALUACION_RESULTADO" VARCHAR,
"ESTADO_SINCRONIZACION" BIGINT,
"FECHA_ACTUALIZACION" timestamp,
"usr_tipo_doc" VARCHAR
);

-- Insertar los datos en la tabla temporal
INSERT INTO tablaTempApr("LMS_ID", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO", "USR_EMAIL","FIC_ID", "ESTADO", "CMP_ID", "REA_ID", "REA_NOMBRE", "ADR_ID", "ADR_EVALUACION_RESULTADO", "ESTADO_SINCRONIZACION","FECHA_ACTUALIZACION","usr_tipo_doc")
SELECT
    u.id AS LMS_ID,
    u.username AS "USR_NUM_DOC",
    u.firstname AS "USR_NOMBRE",
    u.lastname AS "USR_APELLIDO",
    u.email AS "USR_EMAIL",
    mc.idnumber AS "FIC_ID",
    ue.status AS "ESTADO",
    adr."CMP_ID",
    rap."REA_ID",
    rap."REA_NOMBRE",
    adr."ADR_ID",
    adr."ADR_EVALUACION_RESULTADO",
    adr."ESTADO_SINCRONIZACION",
    adr."FECHA_ACTUALIZACION",
    adr."usr_tipo_doc"
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_course mc ON mc.id = e.courseid
JOIN mdl_context ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = 50
JOIN mdl_role_assignments ra ON ra.userid = u.id AND ra.contextid = ctx.id
JOIN mdl_role r ON r.id = ra.roleid
JOIN public."TABLA_NOTASXRA_CC" adr ON u.id = adr."LMS_ID"
JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
WHERE mc.idnumber = curso
AND adr."CMP_ID" = competencia::BIGINT
AND rap."REA_ID" = rea_id::BIGINT
AND r.shortname = 'student'
AND u.id NOT IN (
    SELECT u2.id
    FROM mdl_user u2
    JOIN mdl_role_assignments ra2 ON u2.id = ra2.userid
    JOIN mdl_role r2 ON ra2.roleid = r2.id
    JOIN mdl_context ctx2 ON ra2.contextid = ctx2.id
    WHERE ctx2.instanceid = e.courseid
    AND r2.shortname IN ('editingteacher', 'teacher')
)
ORDER BY u.firstname ASC;


-- Crear la vista a partir de la tabla temporal
CREATE OR REPLACE VIEW vista_result AS
SELECT * FROM tablaTempApr;

END;
$$
LANGUAGE 'plpgsql'; */



---------------------------------------
----------- FUNCION OBTENER RESULTADOS CON ESQUEMA RESULTADOS --------------

CREATE OR REPLACE FUNCTION obtenerResultadosNew(curso VARCHAR, competencia VARCHAR, tabla VARCHAR)
RETURNS VOID AS
$$
BEGIN
    RAISE NOTICE 'Inicio de la función';
    
    -- Crear la tabla temporal
    CREATE TEMPORARY TABLE IF NOT EXISTS tablaTemp(
        "ID_USUARIO_LMS" BIGINT,
        "USR_NUM_DOC" VARCHAR,
        "USR_NOMBRE" VARCHAR,
        "USR_APELLIDO" VARCHAR,
        "USR_EMAIL" VARCHAR,
        "FIC_ID" VARCHAR,
        "ESTADO" BIGINT,
        "CMP_ID" BIGINT,
        "CMP_NOMBRE" VARCHAR,
        "REA_ID" BIGINT,
        "REA_NOMBRE" VARCHAR,
        "ADR_ID" BIGINT,
        "ADR_EVALUACION_RESULTADO" VARCHAR,
        "ESTADO_SINCRONIZACION" BIGINT,
        "usr_tipo_doc" VARCHAR
    );

    -- Crear la consulta dinámica para insertar datos en la tabla temporal
    EXECUTE format('
        INSERT INTO tablaTemp("ID_USUARIO_LMS", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO", "USR_EMAIL", "FIC_ID", "ESTADO", "CMP_ID", "CMP_NOMBRE", "REA_ID", "REA_NOMBRE", "ADR_ID", "ADR_EVALUACION_RESULTADO", "ESTADO_SINCRONIZACION", "usr_tipo_doc")
        SELECT
            u.id AS "ID_USUARIO_LMS",
            u.username AS "USR_NUM_DOC",
            u.firstname AS "USR_NOMBRE",
            u.lastname AS "USR_APELLIDO",
            u.email AS "USR_EMAIL",
            mc.idnumber AS "FIC_ID",
            ue.status AS "ESTADO",
            adr."CMP_ID",
            c."CMP_NOMBRE",
            rap."REA_ID",
            rap."REA_NOMBRE",
            adr."ADR_ID",
            adr."ADR_EVALUACION_RESULTADO",
            adr."ESTADO_SINCRONIZACION",
            adr."usr_tipo_doc"
        FROM mdl_user u
        JOIN mdl_user_enrolments ue ON ue.userid = u.id
        JOIN mdl_enrol e ON e.id = ue.enrolid
        JOIN mdl_role_assignments ra ON ra.userid = u.id
        JOIN mdl_course mc ON mc.id = e.courseid
        JOIN mdl_role r ON r.id = ra.roleid
        JOIN public.%I adr ON u.id = adr."LMS_ID"
        JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
        JOIN public."COMPETENCIA" c ON c."CMP_ID" = adr."CMP_ID"
        WHERE mc.idnumber = $1 AND adr."CMP_ID" = $2::BIGINT
        ORDER BY u.firstname ASC
    ', tabla) USING curso, competencia;

    -- Crear la vista a partir de la tabla temporal
    CREATE OR REPLACE VIEW vista_result AS
    SELECT * FROM tablaTemp;

END;
$$
LANGUAGE 'plpgsql';

------------------- FUNCION OBTENER RESULTADOS APRENDIZ CON ESQUEMA RESULTADOS -----------------------

CREATE OR REPLACE FUNCTION obtenerResultadosAprendizNew(curso VARCHAR, competencia VARCHAR, user_id BIGINT, tabla VARCHAR)
RETURNS VOID AS
$$
begin
RAISE NOTICE 'Inicio de la función';
-- Crear la tabla temporal
CREATE TEMPORARY TABLE IF NOT EXISTS tablaTempApr(
"LMS_ID" BIGINT,
"USR_NUM_DOC" VARCHAR,
"USR_NOMBRE" VARCHAR,
"USR_APELLIDO" VARCHAR,
"USR_EMAIL" VARCHAR,
"FIC_ID" VARCHAR,
"CMP_ID" BIGINT,
"REA_ID" BIGINT,
"REA_NOMBRE" VARCHAR,
"ADR_ID" BIGINT,
"ADR_EVALUACION_RESULTADO" VARCHAR,
"ESTADO_SINCRONIZACION" BIGINT,
"NIS_FUN_EVALUO" BIGINT
);

-- Insertar los datos en la tabla temporal
EXECUTE format('
INSERT INTO tablaTempApr("LMS_ID", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO","USR_EMAIL","FIC_ID", "CMP_ID", "REA_ID", "REA_NOMBRE", "ADR_ID", "ADR_EVALUACION_RESULTADO", "ESTADO_SINCRONIZACION","NIS_FUN_EVALUO")
select DISTINCT
u.id AS LMS_ID,
u.username AS "USR_NUM_DOC",
u.firstname AS "USR_NOMBRE",
u.lastname AS "USR_APELLIDO",
u.email AS "USR_EMAIL",
mc.idnumber AS "FIC_ID",
adr."CMP_ID",
rap."REA_ID",
rap."REA_NOMBRE",
adr."ADR_ID",
adr."ADR_EVALUACION_RESULTADO",
adr."ESTADO_SINCRONIZACION",
adr."NIS_FUN_EVALUO"
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_course mc ON mc.id = e.courseid
JOIN mdl_role r ON r.id = ra.roleid
JOIN public.%I adr ON u.id = adr."LMS_ID"
JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
WHERE mc.idnumber = $1 AND adr."CMP_ID" = $2::BIGINT AND u.id = $3
ORDER BY u.firstname ASC
', tabla) USING curso, competencia, user_id;

-- Crear la vista a partir de la tabla temporal
CREATE OR REPLACE VIEW vista_resultap AS
SELECT * FROM tablaTempApr;

END;
$$
LANGUAGE 'plpgsql';


------------------- FUNCION OBTENER RESULTADOS REA CON ESQUEMA RESULTADOS -----------------------

CREATE OR REPLACE FUNCTION obtenerResultadosReaNew(curso VARCHAR, competencia VARCHAR, rea_id BIGINT, tabla VARCHAR)
RETURNS VOID AS
$$
begin
RAISE NOTICE 'Inicio de la función';
-- Crear la tabla temporal
CREATE TEMPORARY TABLE IF NOT EXISTS tablaTempApr(
"LMS_ID" BIGINT,
"USR_NUM_DOC" VARCHAR,
"USR_NOMBRE" VARCHAR,
"USR_APELLIDO" VARCHAR,
"USR_EMAIL" VARCHAR,
"FIC_ID" VARCHAR,
"ESTADO" BIGINT, 
"CMP_ID" BIGINT,
"REA_ID" BIGINT,
"REA_NOMBRE" VARCHAR,
"ADR_ID" BIGINT,
"ADR_EVALUACION_RESULTADO" VARCHAR,
"ESTADO_SINCRONIZACION" BIGINT,
"FECHA_ACTUALIZACION" timestamp,
"usr_tipo_doc" VARCHAR,
"NIS_FUN_EVALUO" BIGINT
);

    -- Crear la consulta dinámica para insertar datos en la tabla temporal
   EXECUTE format('
INSERT INTO tablaTempApr("LMS_ID", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO", "USR_EMAIL","FIC_ID", "ESTADO", "CMP_ID", "REA_ID", "REA_NOMBRE", "ADR_ID", "ADR_EVALUACION_RESULTADO", "ESTADO_SINCRONIZACION", "FECHA_ACTUALIZACION", "usr_tipo_doc","NIS_FUN_EVALUO")
SELECT
    u.id AS LMS_ID,
    u.username AS "USR_NUM_DOC",
    u.firstname AS "USR_NOMBRE",
    u.lastname AS "USR_APELLIDO",
    u.email AS "USR_EMAIL",
    mc.idnumber AS "FIC_ID",
    ue.status AS "ESTADO",
    adr."CMP_ID",
    rap."REA_ID",
    rap."REA_NOMBRE",
    adr."ADR_ID",
    adr."ADR_EVALUACION_RESULTADO",
    adr."ESTADO_SINCRONIZACION",
    adr."FECHA_ACTUALIZACION",
    adr."usr_tipo_doc",
    adr."NIS_FUN_EVALUO"
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_course mc ON mc.id = e.courseid
JOIN mdl_context ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = 50
JOIN mdl_role_assignments ra ON ra.userid = u.id AND ra.contextid = ctx.id
JOIN mdl_role r ON r.id = ra.roleid
JOIN public.%I adr ON u.id = adr."LMS_ID"
JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
WHERE mc.idnumber = $1
AND adr."CMP_ID" = $2::BIGINT
AND rap."REA_ID" = $3::BIGINT
AND r.shortname = ''student''
AND u.id NOT IN (
    SELECT u2.id
    FROM mdl_user u2
    JOIN mdl_role_assignments ra2 ON u2.id = ra2.userid
    JOIN mdl_role r2 ON ra2.roleid = r2.id
    JOIN mdl_context ctx2 ON ra2.contextid = ctx2.id
    WHERE ctx2.instanceid = e.courseid
    AND r2.shortname IN (''editingteacher'', ''teacher'')
)
ORDER BY u.firstname ASC
', tabla) USING curso, competencia, rea_id;

-- Crear la vista a partir de la tabla temporal
CREATE OR REPLACE VIEW vista_result AS
SELECT * FROM tablaTempApr;

END;
$$
LANGUAGE 'plpgsql';


---------------------------------------------

------------------- FUNCION OBTENER NOMBRE INSTRUCTOR EVALUO -----------------------

CREATE OR REPLACE FUNCTION obtenerNameNisFun(curso VARCHAR, competencia VARCHAR, rea_id BIGINT, tabla VARCHAR)
RETURNS VOID AS
$$
begin
RAISE NOTICE 'Inicio de la función';
-- Crear la tabla temporal
CREATE TEMPORARY TABLE IF NOT EXISTS tablaNisName(
"LMS_ID" BIGINT,
"USR_NUM_DOC" VARCHAR,
"USR_NOMBRE" VARCHAR,
"USR_APELLIDO" VARCHAR,
"NIS_FUN_EVALUO" BIGINT
);

    -- Crear la consulta dinámica para insertar datos en la tabla temporal
   EXECUTE format('
INSERT INTO tablaNisName("LMS_ID", "USR_NUM_DOC", "USR_NOMBRE", "USR_APELLIDO","NIS_FUN_EVALUO")
SELECT
    u.id AS LMS_ID,
    u.username AS "USR_NUM_DOC",
    u.firstname AS "USR_NOMBRE",
    u.lastname AS "USR_APELLIDO",
    adr."NIS_FUN_EVALUO"
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_course mc ON mc.id = e.courseid
JOIN public.%I adr ON u.id = adr."NIS_FUN_EVALUO"
JOIN public."RESULTADO_APRENDIZAJE" rap ON adr."REA_ID" = rap."REA_ID"
WHERE mc.idnumber = $1
AND adr."CMP_ID" = $2::BIGINT
AND rap."REA_ID" = $3::BIGINT
ORDER BY u.firstname ASC
', tabla) USING curso, competencia, rea_id;

-- Crear la vista a partir de la tabla temporal
CREATE OR REPLACE VIEW vista_nisEvaluo AS
SELECT * FROM tablaNisName;

END;
$$
LANGUAGE 'plpgsql';

