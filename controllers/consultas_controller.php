<?php
// consultas_controller.php
require '../config/db_config.php';
// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id_curso = isset($_POST['id_curso']) ? $_POST['id_curso'] : null;
    $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : null;
    $fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : null;
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
    $idcategoria = isset($_POST['categoria']) ? $_POST['categoria'] : null;

    // Validar que los datos se hayan recibido
    if ($id_curso && $fechaInicio && $fechaFin) {
        // Aquí puedes realizar las operaciones que necesites, como consultas a la base de datos

        // FECHA INICIO DEL CURSO
        $fecha_inicio = date('d/m/Y', $fecha);
        list($dia, $mes, $anio) = explode('/', $fecha_inicio);
        // Convertir $mes a entero
        $mes = intval($mes);

        if ($mes <= 06) {
            $semestre = '01'; // Primer semestre
        } else {
            $semestre = '02'; // Segundo semestre
        }

        //OBTENER EL NOMBRE DE LA TABLA DESDE EL CATEGORIA DEL CURSO
        switch ($idcategoria) {
            case 3:
                $formacion = 'C';
                break;
            case 4:
            case 5:
                $formacion = 'T';
                break;
            default:
                // Un valor predeterminado si es necesario
                break;
        }
        //FIN OBTENER EL NOMBRE DE LA TABLA DESDE EL CATEGORIA DEL CURSO

        $tabla = ('RA' . '_' . $formacion . '_' . $anio . '_' . $semestre);

        /////////////////////////////
        //Ojito cambiar a $id_curso
        $varchar = 9723;
        ////////////////////////////

        $excusa = $conn->prepare(query: "SELECT COUNT (*) FROM obtenerExcusaMedica(:id_curso, :fechaI, :fechaF)");
        $excusa->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $excusa->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $excusa->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $excusa->execute();
        $excusaMedica = $excusa->fetchAll(PDO::FETCH_ASSOC);

        $tarde = $conn->prepare(query: "SELECT COUNT (*) FROM obtenerLlegadaTarde(:id_curso, :fechaI, :fechaF)");
        $tarde->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $tarde->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $tarde->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $tarde->execute();
        $llegadaTarde = $tarde->fetchAll(PDO::FETCH_ASSOC);

        $asis = $conn->prepare(query: "SELECT COUNT (*) FROM obtenerAsistencia(:id_curso, :fechaI, :fechaF)");
        $asis->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $asis->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $asis->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $asis->execute();
        $asistencia = $asis->fetchAll(PDO::FETCH_ASSOC);

        $inasis = $conn->prepare(query: "SELECT COUNT (*) FROM obtenerInasistencia(:id_curso, :fechaI, :fechaF)");
        $inasis->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $inasis->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $inasis->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $inasis->execute();
        $inasistencia = $inasis->fetchAll(PDO::FETCH_ASSOC);

        $suspen = $conn->prepare(query: "SELECT COUNT (*) FROM obtenerSuspendidos(:id_curso, :fechaI, :fechaF)");
        $suspen->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $suspen->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $suspen->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $suspen->execute();
        $suspendido = $suspen->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = $conn->prepare("SELECT obtenerUsuarios(:id_curso) AS total_estudiantes");
        $usuarios->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $usuarios->execute();
        $usuario = $usuarios->fetch(PDO::FETCH_ASSOC);
        $total_estudiantes = $usuario['total_estudiantes'];

        $quiz = $conn->prepare("SELECT obtenerparticipacionquiz(:id_curso, :fechaI, :fechaF)");
        $quiz->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $quiz->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $quiz->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $quiz->execute();
        $actividades = $quiz->fetchAll(PDO::FETCH_ASSOC);

        $evi = $conn->prepare("SELECT obtenerparticipacionevi(:id_curso, :fechaI, :fechaF)");
        $evi->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $evi->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $evi->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $evi->execute();
        $evidencias = $evi->fetchAll(PDO::FETCH_ASSOC);

        $forum = $conn->prepare("SELECT obtenerparticipacionforum(:id_curso, :fechaI, :fechaF)");
        $forum->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $forum->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $forum->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $forum->execute();
        $foros = $forum->fetchAll(PDO::FETCH_ASSOC);

        $wik = $conn->prepare("SELECT obtenerparticipacionwiki(:id_curso, :fechaI, :fechaF)");
        $wik->bindParam(':id_curso', $varchar, PDO::PARAM_INT);
        $wik->bindParam(':fechaI', $fechaInicio, PDO::PARAM_STR);
        $wik->bindParam(':fechaF', $fechaFin, PDO::PARAM_STR);
        $wik->execute();
        $wikis = $wik->fetchAll(PDO::FETCH_ASSOC);


        // Ejemplo: Imprimir los datos recibidos
        $response = [
            'success' => true,
            'message' => 'Datos recibidos correctamente.',
            'data' => [
                'excusaMedica' => $excusaMedica,
                'llegadaTarde' => $llegadaTarde,
                'asistencia' => $asistencia,
                'inasistencia' => $inasistencia,
                'suspendido' => $suspendido,
                'estudiantes' => $total_estudiantes,
                'actividades' => $actividades,
                'evidencias' => $evidencias,
                'foros' => $foros,
                'wikis' => $wikis,
                'tabla' => $tabla
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
