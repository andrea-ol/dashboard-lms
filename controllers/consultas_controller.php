<?php
// consultas_controller.php

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id_curso = isset($_POST['id_curso']) ? $_POST['id_curso'] : null;
    $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : null;
    $fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : null;

    // Validar que los datos se hayan recibido
    if ($id_curso && $fechaInicio && $fechaFin) {
        // Aquí puedes realizar las operaciones que necesites, como consultas a la base de datos

        // Ejemplo: Imprimir los datos recibidos
        $response = [
            'success' => true,
            'message' => 'Datos recibidos correctamente.',
            'data' => [
                'id_curso' => $id_curso,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin
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
?>
