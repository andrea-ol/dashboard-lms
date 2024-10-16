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
function obtenerCursos($centroFormacion, $tipoFormacion)
{
    global $replica, $errorPage, $conn;
    try {
        // Llamada a la función para obtener los datos de los cursos con "Formación"
        $tipo = $conn->prepare("SELECT * FROM obtenerCursos(:centroF, :tipoF)");
        $tipo->bindParam(':centroF', $centroFormacion, PDO::PARAM_INT);
        $tipo->bindParam(':tipoF', $tipoFormacion, PDO::PARAM_STR);
        $tipo->execute();
        $cursos = $tipo->fetchAll(PDO::FETCH_ASSOC);
        // Retornar los resultados de la consulta
        return $cursos;
    } catch (PDOException $e) {
        echo "Error al obtener la formación: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}


