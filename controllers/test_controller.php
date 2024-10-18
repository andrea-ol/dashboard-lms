<?php

/* Configuración de la conexión a la Base de Datos utilizando las variables de entorno */
$rutaServidor = '127.0.0.1';
$puerto = '5432';
$usuario = 'postgres';
$password = '12345';
$nombreBaseDeDatos = 'integracion_replica-v3';

/* Validación de la conexión */
try {
    /* Validación de la conexión */
    $replica = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $password);
    $replica->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    /* PDO es un controlador que implementa la interfaz de Objetos de Datos de PHP (PDO), permitir el acceso desde PHP a bases de datos de PostgreSQL */
} catch (PDOException $e) {

    echo "Error al conectarse a la base de datos: " . $e->getMessage() . "\n";
    echo "<script>
            window.location.href = '/dashboard-lms/error/error_conexion.php';
        </script>";
}

/////////////////////////////
//Ojito cambiar a $id_curso
$varchar = 9723;
//Ojito cambiar a $tabla
$varchar2 = 'RA_T_2024_01';
//Ojito cambiar a $idnumber
$varchar3 = '2963261';
////////////////////////////
$fechaInicio = '2024-09-01';
$fechaFin = '2024-10-18';

// Prepara la consulta
$resultados = $replica->prepare("SELECT DISTINCT CMP_ID, REA_ID FROM \"INTEGRACION\".obtenerFicReaId(:id_curso)");
$resultados->bindParam(':id_curso', $varchar3, PDO::PARAM_STR);
$resultados->execute();
$comres = $resultados->fetchAll(PDO::FETCH_ASSOC);

// Inicializa los arrays para CMP_ID y REA_ID
$cmp_array = [];
$rea_array = [];

// Itera sobre los resultados de la consulta
foreach ($comres as $row) {
    // Verifica si las claves CMP_ID y REA_ID están presentes para evitar errores
    if (isset($row['cmp_id']) && isset($row['rea_id'])) {
        $cmp_array[] = $row['cmp_id']; // Agrega el valor de CMP_ID al array
        $rea_array[] = $row['rea_id']; // Agrega el valor de REA_ID al array
    }
}

$stmt = $replica->prepare("SELECT competencia, resultado FROM \"INTEGRACION\".obtener_resultados(:idnumber, :cmp_id, :rea_id, :fechaInicio, :fechaFin, :tabla)"); 
$stmt->bindParam(':idnumber', $varchar3, PDO::PARAM_STR);
$stmt->bindValue(':cmp_id', '{' . implode(',', $cmp_array) . '}', PDO::PARAM_STR);
$stmt->bindValue(':rea_id', '{' . implode(',', $rea_array) . '}', PDO::PARAM_STR);
$stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
$stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
$stmt->bindParam(':tabla', $varchar2, PDO::PARAM_STR);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Array para almacenar competencias con sus resultados
$competencias = [];

foreach ($results as $row) {
    $competencia = $row['competencia'];
    $resultado = $row['resultado'];

    // Si la competencia no existe en el array, inicialízala
    if (!array_key_exists($competencia, $competencias)) {
        $competencias[$competencia] = [];
    }

    // Agregar el resultado de aprendizaje a la competencia correspondiente
    $competencias[$competencia][] = $resultado;
}
// 

// Ejemplo de impresión de los resultados para depuración : [ 'competencia1' => [ 'resultado1', 'resultado2', ...], 'competencia2' => [ 'resultado1', ...], ...]
print_r($competencias);


var_dump($competencias);
var_dump($resultados);
