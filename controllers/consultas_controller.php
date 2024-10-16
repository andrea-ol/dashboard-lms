<?php
// consultas_controller.php
require '../config/db_config.php';
// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id_curso = isset($_POST['id_curso']) ? $_POST['id_curso'] : null;
    $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : null;
    $fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : null;

    // Validar que los datos se hayan recibido
    if ($id_curso && $fechaInicio && $fechaFin) {
        // Aquí puedes realizar las operaciones que necesites, como consultas a la base de datos
        $varchar = '9723';
        $stmt = $conn->prepare(query: "SELECT * FROM obtenerExcusaMedica(:id_curso, :fechaI, :fechaF)");
        $stmt->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $stmt->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $stmt->execute();
        $excusaMedica = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // Ejemplo: Imprimir los datos recibidos
        $response = [
            'success' => true,
            'message' => 'Datos recibidos correctamente.',
            'data' => [
                'id_curso' => $id_curso,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'ExcusaMedica' => $excusaMedica
            ]
        ];
    } else {
        // Manejar el caso en que no se recibieron todos los datos
        $response = [
            'success' => false,
            'message' => 'Faltan datos necesarios.'
        ];
    }

    // Enviar la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Manejar el caso en que la solicitud no es POST
    $response = [
        'success' => false,
        'message' => 'Método de solicitud no permitido.'
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
}
