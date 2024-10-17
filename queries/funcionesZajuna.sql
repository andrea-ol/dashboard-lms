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


----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtenerExcusaMedica(id_curso BIGINT, fechaInicio DATE, fechaFin DATE
)
RETURNS TABLE(
    course_id BIGINT, 
    student_id BIGINT, 
    full_attendance TEXT
) 
AS $$
BEGIN
    RETURN QUERY
    
    SELECT l.course_id, l.student_id, l.full_attendance
    FROM mdl_local_asistencia_permanente l
    WHERE l.course_id = id_curso
    
      AND EXISTS (
          SELECT 1
          FROM jsonb_array_elements(l.full_attendance::jsonb) AS asistencia
          WHERE asistencia->>'ATTENDANCE' = '3'
            AND (asistencia->>'DATE')::DATE BETWEEN fechaInicio AND fechaFin  -- Conversión de texto a fecha
      );
END;
$$ LANGUAGE plpgsql;


---------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtenerLlegadaTarde(id_curso BIGINT, fechaInicio DATE, fechaFin DATE
)
RETURNS TABLE(
    course_id BIGINT, 
    student_id BIGINT, 
    full_attendance TEXT
) 
AS $$
BEGIN
    RETURN QUERY
    
    SELECT l.course_id, l.student_id, l.full_attendance
    FROM mdl_local_asistencia_permanente l
    WHERE l.course_id = id_curso
    
      AND EXISTS (
          SELECT 1
          FROM jsonb_array_elements(l.full_attendance::jsonb) AS asistencia
          WHERE asistencia->>'ATTENDANCE' = '2'
            AND (asistencia->>'DATE')::DATE BETWEEN fechaInicio AND fechaFin  -- Conversión de texto a fecha
      );
END;
$$ LANGUAGE plpgsql;


-------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION obtenerAsistencia(id_curso BIGINT, fechaInicio DATE, fechaFin DATE
)
RETURNS TABLE(
    course_id BIGINT, 
    student_id BIGINT, 
    full_attendance TEXT
) 
AS $$
BEGIN
    RETURN QUERY
    
    SELECT l.course_id, l.student_id, l.full_attendance
    FROM mdl_local_asistencia_permanente l
    WHERE l.course_id = id_curso
    
      AND EXISTS (
          SELECT 1
          FROM jsonb_array_elements(l.full_attendance::jsonb) AS asistencia
          WHERE asistencia->>'ATTENDANCE' = '1'
            AND (asistencia->>'DATE')::DATE BETWEEN fechaInicio AND fechaFin  -- Conversión de texto a fecha
      );
END;
$$ LANGUAGE plpgsql;


---------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION obtenerInasistencia(id_curso BIGINT, fechaInicio DATE, fechaFin DATE
)
RETURNS TABLE(
    course_id BIGINT, 
    student_id BIGINT, 
    full_attendance TEXT
) 
AS $$
BEGIN
    RETURN QUERY
    
    SELECT l.course_id, l.student_id, l.full_attendance
    FROM mdl_local_asistencia_permanente l
    WHERE l.course_id = id_curso
    
      AND EXISTS (
          SELECT 1
          FROM jsonb_array_elements(l.full_attendance::jsonb) AS asistencia
          WHERE asistencia->>'ATTENDANCE' = '0'
            AND (asistencia->>'DATE')::DATE BETWEEN fechaInicio AND fechaFin  -- Conversión de texto a fecha
      );
END;
$$ LANGUAGE plpgsql;


--------------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION obtenerSuspendidos(id_curso BIGINT, fechaInicio DATE, fechaFin DATE
)
RETURNS TABLE(
    course_id BIGINT, 
    student_id BIGINT, 
    full_attendance TEXT
) 
AS $$
BEGIN
    RETURN QUERY
    
    SELECT l.course_id, l.student_id, l.full_attendance
    FROM mdl_local_asistencia_permanente l
    WHERE l.course_id = id_curso
    
      AND EXISTS (
          SELECT 1
          FROM jsonb_array_elements(l.full_attendance::jsonb) AS asistencia
          WHERE asistencia->>'ATTENDANCE' = '-1'
            AND (asistencia->>'DATE')::DATE BETWEEN fechaInicio AND fechaFin  -- Conversión de texto a fecha
      );
END;
$$ LANGUAGE plpgsql;


----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtenerUsuarios(course BIGINT)
RETURNS BIGINT AS
$$
DECLARE
    total_estudiantes BIGINT;
BEGIN
    -- Contar el número total de estudiantes en el curso especificado
    SELECT COUNT(*)
    INTO total_estudiantes
    FROM mdl_user u
    JOIN mdl_user_enrolments ue ON u.id = ue.userid
    JOIN mdl_enrol e ON ue.enrolid = e.id
    JOIN mdl_context ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = 50
    JOIN mdl_role_assignments ra ON u.id = ra.userid AND ra.contextid = ctx.id
    JOIN mdl_role r ON ra.roleid = r.id
    WHERE e.courseid = course 
      AND r.shortname = 'student'
      AND u.id NOT IN (
          SELECT u2.id
          FROM mdl_user u2
          JOIN mdl_role_assignments ra2 ON u2.id = ra2.userid
          JOIN mdl_role r2 ON ra2.roleid = r2.id
          JOIN mdl_context ctx2 ON ra2.contextid = ctx2.id
          WHERE ctx2.instanceid = e.courseid
          AND r2.shortname IN ('editingteacher', 'teacher')
      );

    -- Retornar el total de estudiantes
    RETURN total_estudiantes;
END;
$$
LANGUAGE 'plpgsql';


-----------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION obtenerparticipacionevi(id_curso BIGINT, fechaInicio DATE, fechaFin DATE)
RETURNS BIGINT AS $$
DECLARE
    participacion_count BIGINT;
BEGIN
    SELECT count(a.id)
    INTO participacion_count
    FROM mdl_assign_submission s
    JOIN mdl_assign a ON a.id = s.assignment
    WHERE s.status = 'submitted'
    AND a.course = id_curso
    -- Incluir el tiempo final hasta las 23:59:59 del día
    AND s.timecreated BETWEEN EXTRACT(EPOCH FROM fechaInicio) 
                          AND EXTRACT(EPOCH FROM fechaFin + INTERVAL '1 day') - 1;
    
    RETURN participacion_count;
END;
$$ LANGUAGE plpgsql;


------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION obtenerparticipacionquiz(id_curso BIGINT, fechaInicio DATE, fechaFin DATE
)
RETURNS BIGINT AS $$
DECLARE
    participacion_count BIGINT;
BEGIN
    SELECT count(q.id)
    INTO participacion_count
    FROM mdl_quiz_grades qg
    JOIN mdl_quiz q ON qg.quiz = q.id
    WHERE q.course = id_curso
    AND qg.timemodified BETWEEN EXTRACT(EPOCH FROM fechaInicio) 
                             AND EXTRACT(EPOCH FROM fechaFin + INTERVAL '1 day') - 1;
    
    RETURN participacion_count;
END;
$$ LANGUAGE plpgsql;


----------------------------------------------------------------------------------



CREATE OR REPLACE FUNCTION obtenerparticipacionforum(
    id_curso BIGINT,
    fechaInicio DATE,
    fechaFin DATE
)
RETURNS BIGINT AS $$
DECLARE
    participacion_count BIGINT;
BEGIN
    SELECT count(fp.id)
    INTO participacion_count
    FROM mdl_forum_posts fp
    JOIN mdl_forum_discussions fd ON fp.discussion = fd.id
    JOIN mdl_forum f ON fd.forum = f.id
    WHERE f.course = id_curso
    AND fp.created BETWEEN EXTRACT(EPOCH FROM fechaInicio) 
                       AND EXTRACT(EPOCH FROM fechaFin + INTERVAL '1 day') - 1;

    RETURN participacion_count;
END;
$$ LANGUAGE plpgsql;



------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION obtenerparticipacionwiki(
    id_curso BIGINT,
    fechaInicio DATE,
    fechaFin DATE
)
RETURNS BIGINT AS $$
DECLARE
    participacion_count BIGINT;
BEGIN
    SELECT count(w.id)
    INTO participacion_count
    FROM mdl_wiki_versions wv
    JOIN mdl_wiki_pages wp ON wp.id = wv.pageid        
    JOIN mdl_wiki_subwikis ws ON ws.id = wp.subwikiid 
    JOIN mdl_wiki w ON w.id = ws.wikiid              
    WHERE w.course = id_curso
    AND wv.timecreated BETWEEN EXTRACT(EPOCH FROM fechaInicio) 
                           AND EXTRACT(EPOCH FROM fechaFin + INTERVAL '1 day') - 1;
    
    RETURN participacion_count;
END;
$$ LANGUAGE plpgsql;