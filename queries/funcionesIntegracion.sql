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

CREATE OR REPLACE FUNCTION "INTEGRACION".obtener_resultados(idnumber VARCHAR, cmp_id BIGINT[], rea_id BIGINT[], fecha_inicio DATE, fecha_fin DATE, tabla VARCHAR)

RETURNS TABLE (
    fic_id BIGINT,
    competencia BIGINT,
    resultado BIGINT,
    fecha_envio TIMESTAMP  
)

AS $$
DECLARE
    i INT;
    query TEXT;
begin
	
    FOR i IN 1..array_length(cmp_id, 1) LOOP
        
	    query := format('
            SELECT rt."FIC_ID", rt."CMP_ID", rt."REA_ID", rt."FECHA_ENVIO_SOFIA"
            FROM "RESULTADOS".%I rt
            JOIN "INTEGRACION"."COMPETENCIA" c2 ON c2."CMP_ID" = rt."CMP_ID"
            WHERE rt."FIC_ID" = $1::BIGINT
              AND rt."CMP_ID" = $2
              AND rt."REA_ID" = $3
              AND c2."CMP_ACTIVO" = ''1''
              AND rt."FECHA_ENVIO_SOFIA"::DATE BETWEEN $4 AND $5
        ', tabla);

        RETURN QUERY EXECUTE query
        USING idnumber, cmp_id[i], rea_id[i], fecha_inicio, fecha_fin;
    END LOOP;
END;
$$ LANGUAGE plpgsql;
