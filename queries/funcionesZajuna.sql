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
    idcate BIGINT,
    categoria VARCHAR,
    fecha_inicio BIGINT,
    fecha_fin BIGINT
) AS $$

BEGIN
    RETURN QUERY
    SELECT c.id, c.fullname, c.shortname, c.idnumber, c.category, cc.name, c.startdate, c.enddate
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

----------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtenerFicReaId(course VARCHAR)
RETURNS TABLE (
    cmp_id bigint,
    rea_id bigint
) AS $$
BEGIN
    RETURN QUERY
    SELECT c2."CMP_ID", r."REA_ID"
    FROM "RESULTADOS"."RA_T_2024_01" r
    JOIN "INTEGRACION"."COMPETENCIA" c2 ON c2."CMP_ID" = r."CMP_ID"
    WHERE r."FIC_ID" = course::bigint  -- course es equivalente a FIC_ID
    AND c2."CMP_ACTIVO" = '1';
END;
$$ LANGUAGE plpgsql;


---------------------------------------------------------------------------

-- FUNCIONES ANALISIS -----------------------------------------------------

CREATE OR REPLACE FUNCTION analizar_asistencia(p_course_id BIGINT, p_semana_inicio DATE, p_semana_fin DATE)
RETURNS TABLE (
    student_id BIGINT,
    aprendiz TEXT,
    condicion TEXT
)
LANGUAGE plpgsql
AS $$
DECLARE
    r RECORD;
    attendance_record jsonb;
    inasistencias_seguidas INT;
    inasistencias_intermitentes INT;
    total_horas_tarde INT;
BEGIN
    -- Recorrer cada estudiante en el curso indicado
    FOR r IN (
        SELECT p.student_id AS sid, u.firstname || ' ' || u.lastname AS fullname, p.full_attendance
        FROM mdl_local_asistencia_permanente p
        JOIN mdl_user u ON u.id = p.student_id
        WHERE p.course_id = p_course_id
          AND p.full_attendance IS NOT NULL
    ) LOOP
        -- Inicializar contadores
        inasistencias_seguidas := 0;
        inasistencias_intermitentes := 0;
        total_horas_tarde := 0;

        -- Extraer las entradas de asistencia de full_attendance
        FOR attendance_record IN
            SELECT * FROM jsonb_array_elements_text(r.full_attendance::jsonb)
        LOOP
            -- Obtener los valores de asistencia y fecha
            IF (attendance_record->>'DATE')::date BETWEEN p_semana_inicio AND p_semana_fin THEN
                CASE attendance_record->>'ATTENDANCE'
                    WHEN '0' THEN
                        -- Contar inasistencias consecutivas
                        inasistencias_seguidas := inasistencias_seguidas + 1;

                        -- Verificar inasistencias intermitentes (condición para intermitencia)
                        IF inasistencias_seguidas = 1 THEN
                            inasistencias_intermitentes := inasistencias_intermitentes + 1;
                        END IF;

                    WHEN '2' THEN
                        -- Reiniciar contador de inasistencias seguidas
                        inasistencias_seguidas := 0;

                        -- Sumar horas de llegada tarde
                        total_horas_tarde := total_horas_tarde + (attendance_record->>'AMOUNTHOURS')::int;

                    ELSE
                        -- Si no es ni '0' (inasistencia) ni '2' (llegada tarde), reiniciar el contador de inasistencias seguidas
                        inasistencias_seguidas := 0;
                END CASE;

                -- Condiciones para inasistencias intermitentes
                IF inasistencias_intermitentes >= 2 THEN
                    RETURN QUERY SELECT r.sid, r.fullname, 'Inasistencias intermitentes';
                END IF;

                -- Condiciones para 3 inasistencias seguidas
                IF inasistencias_seguidas = 3 THEN
                    RETURN QUERY SELECT r.sid, r.fullname, '3 inasistencias seguidas';
                END IF;

                -- Condiciones para llegadas tarde recurrentes con horas acumuladas >= 5
                IF total_horas_tarde >= 5 THEN
                    RETURN QUERY SELECT r.sid, r.fullname, 'Llegadas tarde recurrentes (>= 5 horas acumuladas)';
                END IF;
            END IF;
        END LOOP;
    END LOOP;
END $$;

-----------------------------------------------------------------------