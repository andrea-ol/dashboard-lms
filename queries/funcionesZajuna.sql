CREATE OR REPLACE FUNCTION obtenerUsuarioDash(iduser BIGINT)
RETURNS VOID AS
$$
begin
	RAISE NOTICE 'Inicio de la función';
    -- Crear la tabla temporal
    CREATE TEMPORARY TABLE IF NOT EXISTS tablaTempUsDash(
        userid BIGINT,
        username VARCHAR,
        firstname VARCHAR,
        lastname VARCHAR,
        email VARCHAR,
        institution VARCHAR,
        shortname VARCHAR,
        tipo_user BIGINT
    );

    -- Insertar los datos en la tabla temporal
    INSERT INTO tablaTempUsDash(userid, username, firstname, lastname, email, institution, shortname, tipo_user)
    
    SELECT DISTINCT 
    u.id as userid, 
    u.username, 
    u.firstname, 
    u.lastname, 
    u.email, 
    u.institution,
    r.shortname, 
    ra.roleid as tipo_user
    
    FROM mdl_user u
    JOIN mdl_user_enrolments ue ON ue.userid = u.id
    JOIN mdl_enrol e ON e.id = ue.enrolid
    JOIN mdl_role_assignments ra ON ra.userid = u.id
    JOIN mdl_course mc ON mc.id = e.courseid
    JOIN mdl_role r ON r.id = ra.roleid
    WHERE u.id = iduser;

    -- Crear la vista a partir de la tabla temporal
    CREATE OR REPLACE VIEW vista_usuario_dash AS 
    SELECT * FROM tablaTempUsDash;
   
END;
$$
LANGUAGE 'plpgsql';

-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtenerFormacion()
RETURNS TABLE(nombre VARCHAR) AS
$$
BEGIN
    RETURN QUERY
    SELECT name 
    FROM mdl_course_categories
    WHERE name LIKE '%Formación%';
END;
$$
LANGUAGE 'plpgsql';

---------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtenerCursos(centroformacion VARCHAR, tipoformacion VARCHAR)
RETURNS TABLE (
    id BIGINT,
    fullname VARCHAR,
    shortname VARCHAR,
    idnumber VARCHAR,
    categoria VARCHAR,
    fecha_inicio BIGINT,
    fecha_fin BIGINT
) AS $$

BEGIN
    RETURN QUERY
    SELECT c.id, c.fullname, c.shortname, c.idnumber, cc.name, c.startdate, c.enddate
    FROM mdl_course c
    JOIN mdl_course_categories cc ON cc.id = c.category
    WHERE c.shortname LIKE '%' || centroformacion || '%' 
    AND cc.name = tipoformacion;
END;

$$ LANGUAGE plpgsql;