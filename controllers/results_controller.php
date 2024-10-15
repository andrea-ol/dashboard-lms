<?php
include "ruta_error.php";

function log_error($replica, $type, $code, $description)
{
    // Preparar la declaración SQL para insertar el registro de error
    $query = "INSERT INTO \"LOG\".error_log (error_type, error_code, error_description, error_date) VALUES (:type, :code, :description, NOW())";
    $stmt = $replica->prepare($query);
    // Ejecutar la declaración con los parámetros
    try {
        $stmt->execute([
            ':type' => $type,
            ':code' => $code,
            ':description' => $description
        ]);
    } catch (PDOException $e) {
        echo "Error al insertar el registro: " . $e->getMessage();
    }
}

// FUNCION PARA PERMITIR EL INGRESO DEL USUARIO A UN CURSO EN CUESTION
function ingreso($id_curso, $user_id)
{
    global $replica, $errorPage, $conn;
    try {
        // Llamada a la función para obtener los parámetros de redirección a letras de calificación de la ficha en cuestión
        $ingreso = $conn->prepare("SELECT obtenerIngreso(:curso, :user)");
        $ingreso->bindParam(':curso', $id_curso, PDO::PARAM_INT);
        $ingreso->bindParam(':user', $user_id, PDO::PARAM_INT);
        $ingreso->execute();
        $ingre_query = "SELECT * FROM vista_ing";
        $ingreso = $conn->prepare($ingre_query);
        $ingreso->execute();
        $ingre = $ingreso->fetchAll(PDO::FETCH_ASSOC);
        return $ingre;
    } catch (PDOException $e) {
        echo "Usuario no matriculado en curso: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}

function obtenerResultadosxCompetencia($curso, $id_competencia, $tabla)
{
    global $conn, $errorPage, $replica;

    try {
        // Llamar a la función PL/pgSQL con los parámetros
        $query = $conn->prepare('SELECT obtenerResultadosNew(:curso, :competencia, :tabla)');
        $query->bindParam(':curso', $curso, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':tabla', $tabla, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':competencia', $id_competencia, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->execute();
        // Consultar la vista creada por la función
        $resul_query = 'SELECT * FROM vista_result';
        $query = $conn->prepare($resul_query);
        $query->execute();
        // Obtener los resultados
        $resultados = $query->fetchAll(PDO::FETCH_OBJ);
        return $resultados;
    } catch (Exception $e) {
        echo "Error al ejecutar la consulta de resultados de aprendizaje: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}

function obtenerResultadosxCompetenciaxApr($curso, $id_competencia, $user_id, $tabla)
{
    global $conn, $errorPage, $replica;

    try {
        // Llamar a la función PL/pgSQL con los parámetros
        $query = $conn->prepare('SELECT obtenerResultadosAprendizNew(:curso, :competencia, :id, :tabla)');
        $query->bindParam(':curso', $curso, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':competencia', $id_competencia, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':tabla', $tabla, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':id', $user_id, PDO::PARAM_INT);
        $query->execute();
        // Consultar la vista creada por la función
        $resul_query = 'SELECT * FROM vista_resultap';
        $query = $conn->prepare($resul_query);
        $query->execute();
        // Obtener los resultados
        $resultadosApren = $query->fetchAll(PDO::FETCH_OBJ);
        return $resultadosApren;
    } catch (Exception $e) {
        echo "Error al ejecutar la consulta de resultados de aprendizaje: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}

function nombre_ficha($curso)
{
    global $conn, $errorPage, $replica;
    try {
        // Preparar la consulta SQL con un placeholder
        $query = $conn->prepare("SELECT c.id, c.fullname, c.idnumber, c.startdate, c.shortname, c.category, cc.id as idcate, cc.name
        FROM mdl_course c
        JOIN mdl_course_categories cc ON c.category = cc.id
        WHERE c.idnumber = :curso");
        // Ejecutar la consulta con el valor del placeholder
        $query->execute(['curso' => $curso]);
        // Obtener los resultados
        $name = $query->fetchAll(PDO::FETCH_OBJ);

        return $name;
    } catch (PDOException $e) {
        echo "Error al obtener el nombre de la ficha: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}


function obtenerNis($curso, $id_competencia, $rea_id, $tabla)
{
    global $conn, $errorPage, $replica;

    try {
        // Llamar a la función PL/pgSQL con los parámetros
        $query = $conn->prepare('SELECT obtenerNameNisFun(:curso, :competencia, :id_rea, :tabla)');
        $query->bindParam(':curso', $curso, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':competencia', $id_competencia, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':tabla', $tabla, PDO::PARAM_STR); // Se usa PARAM_STR porque los parámetros se pasan como VARCHAR
        $query->bindParam(':id_rea', $rea_id, PDO::PARAM_INT);
        $query->execute();
        // Consultar la vista creada por la función
        $resul_query = 'SELECT * FROM vista_nisEvaluo';
        $query = $conn->prepare($resul_query);
        $query->execute();
        // Obtener los resultados
        $nameNisFun = $query->fetchAll(PDO::FETCH_OBJ);
        return $nameNisFun;
    } catch (Exception $e) {
        echo "Error al ejecutar la consulta de resultados de aprendizaje: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}
