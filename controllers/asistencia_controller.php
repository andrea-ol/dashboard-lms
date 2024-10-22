<?php

// FUNCION PARA ALMACENAR ERRORES EN LA BASE DE DATOS DE INTEGRACION EN EL ESQUEMA LOG
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

// FUNCION PARA OBTENER EL NOMBRE DEL CURSO EN CUESTION 
function obtener_asistencia($id_curso)
{
    global $replica, $errorPage, $conn;
    try {
        $query = $conn->prepare("SELECT * FROM obtener_asistencia_curso(:curso)");
        $query->execute([':curso' => $id_curso]);
        $asistencia = $query->fetchAll(PDO::FETCH_ASSOC);
        return $asistencia;
    } catch (PDOException $e) {
        echo "Error al obtener el nombre de la ficha: " . $e->getMessage() . "\n";
        log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
        echo "<meta http-equiv='refresh' content='0;url=$errorPage'>";
        exit();
    }
}
