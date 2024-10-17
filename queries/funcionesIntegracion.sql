--------- funcion para obtener las competencia y resultados calificados
CREATE OR REPLACE FUNCTION "INTEGRACION".obtenerFicReaId(course VARCHAR)
RETURNS TABLE (
    CMP_ID BIGINT,
    REA_ID BIGINT
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

------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION obtener_resultados(
    curso_id VARCHAR,
    cmp_id BIGINT[],
    rea_id BIGINT[],
    fecha_inicio DATE,
    fecha_fin DATE
)
RETURNS TABLE (
    fic_id BIGINT,
    cmp_id BIGINT,
    rea_id BIGINT,
    fecha_envio DATE,
    other_columns TEXT -- Adjust with actual column names
) AS $$
DECLARE
    i INT;
BEGIN

    -- Iterate through the arrays and execute the query for each pair of elements
    FOR i IN 1..array_length(cmp_id, 1) LOOP
        RETURN QUERY
        SELECT rt."FIC_ID", rt."CMP_ID", rt."REA_ID", rt."FECHA_ENVIO_SOFIA", other_columns -- Adjust with actual column names
        FROM "RESULTADOS"."RA_T_2024_01" rt
        JOIN "INTEGRACION"."COMPETENCIA" c2 ON c2."CMP_ID" = rt."CMP_ID"
        WHERE rt."FIC_ID" = curso_id::BIGINT
          AND rt."CMP_ID" = cmp_id[i]
          AND rt."REA_ID" = rea_id[i]
          AND c2."CMP_ACTIVO" = '1'
          AND rt."FECHA_ENVIO_SOFIA" BETWEEN fecha_inicio AND fecha_fin;
    END LOOP;

END;
$$ LANGUAGE plpgsql;
