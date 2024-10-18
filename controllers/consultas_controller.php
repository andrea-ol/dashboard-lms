<?php
// consultas_controller.php
require '../config/db_config.php';
require '../config/sofia_config.php';
// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id_curso = isset($_POST['id_curso']) ? $_POST['id_curso'] : null;
    $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : null;
    $fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : null;
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
    $idcategoria = isset($_POST['categoria']) ? $_POST['categoria'] : null;
    $idnumber = isset($_POST['idnumber']) ? $_POST['idnumber'] : null;

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
        //Ojito cambiar a $tabla
        $varchar2 = 'RA_T_2024_01';
        //Ojito cambiar a $idnumber
        $varchar3 = 2963261;
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

        $stmt = $replica->prepare("SELECT * FROM \"INTEGRACION\".obtener_resultados(:idnumber, :cmp_id, :rea_id, :fechaInicio, :fechaFin, :tabla)");
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
                'competencias' => $competencias
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
