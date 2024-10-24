<?php
// Importar el controlador de sesiones para gestionar las sesiones de usuario
require_once '../controllers/session_controller.php';
require_once '../vendor/autoload.php';
// Incluir configuración de base de datos y otros recursos necesarios
require_once '../config/db_config.php';
require_once '../config/sofia_config.php';
// Incluir el controlador necesario para gestionar las categorias
require_once '../controllers/asistencia_controller.php';


session_start(); // Iniciar la sesión
// Incluir el header de la página
include '../header.php';

/////////////////////////////
//Ojito cambiar a $id_curso
$varchar = 9723;

// Intentar ejecutar el bloque de código y capturar posibles errores relacionados con la sesión
try {
    // Verificar si el usuario está autenticado y si la sesión no ha caducado
    if (isset($_SESSION['user']) && checkSessionTimeout()) {
        // Verificar si la solicitud es POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //OJITO LUEGO CAMBIAR
            //$id_curso = $_POST['fic_id'];
            $id_curso = $varchar;
        }
        // Intentar validar la pertenencia del usuario al curso y capturar posibles errores
        try {
            // Función para calcular el rango de fechas de una semana específica
            function getWeekStartEnd($weekNumber, $year)
            {
                $startOfYear = new DateTime("$year-01-01");

                // Ajustar el día de la semana a lunes (si no es lunes, la semana 1 podría no empezar en el primer día del año)
                if ($startOfYear->format('N') != 1) {
                    $startOfYear->modify('next monday');
                }

                // Calcular la fecha de inicio de la semana
                $daysOffset = ($weekNumber - 1) * 7;
                $startDate = clone $startOfYear;
                $startDate->modify("+$daysOffset days");

                // Calcular la fecha de fin de la semana
                $endDate = clone $startDate;
                $endDate->modify('+6 days');

                return [
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d')
                ];
            }

            // Obtener la semana seleccionada desde el formulario (POST)
            $selectedWeek = isset($_POST['weekSelect']) ? intval($_POST['weekSelect']) : 1;
            $currentYear = date('Y'); // Año actual

            // Obtener el rango de fechas de la semana seleccionada
            $weekRange = getWeekStartEnd($selectedWeek, $currentYear);

            // Variables con fechas de inicio y fin
            $startDate = $weekRange['startDate'];
            $endDate = $weekRange['endDate'];
            // Si el usuario pertenece al curso, se muestra la vista correspondiente
?>
            <main>
                <!-- Título principal de la página -->
                <h5 class="p-2 text-center bg-primary text-white">Detalle de Asistencia</h5>

                <!-- Contenedor para el historial de navegación -->
                <div class="history-container my-2">
                    <?php
                    // Función para mostrar el historial de navegación en cada página
                    mostrar_historial('dashboard-lms');
                    ?>
                </div>

                <div class="container-fluid px-4 my-4">
                    <div class="card p-2 p-md-5">
                        <!-- Encabezado de la tarjeta principal -->
                        <div class="container-fluid container-hearder">
                            <div class="row">
                                <div class="col-sm-10">
                                    <!-- Botón para regresar al curso anterior -->
                                    <h6 class="flex-wrap lang_sign">
                                        <div tabindex="0" role="button" aria-label="Regresar a Categorias">
                                            <img src="../public/assets/img/icno-de-regresar.svg" alt="Ícono de regresar"
                                                style="margin-right: 5px;">
                                            <u id="titulo-regresar">Regresar </u>
                                        </div>
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <!-- Boton de regresar a la pagina anterior (ZAJUNA) -->
                        <div class="card m-4">
                            <ol class="breadcrumb m-2">
                                <!-- TITULO CON BIENVENIDA AL USUARIO INGRESADO  -->
                                <li class="m-2"><strong>Bienvenido/a</strong>
                                    <?php echo mb_strtoupper($user->firstname . ' ' . $user->lastname, 'UTF-8'); ?>
                                </li>
                            </ol>
                            <!-- Control de asistencias -->
                            <div class="row" id="cardschart">
                                <div class="col-xl-12 col-lg-12">
                                    <!-- Card que continen la tabla de asistencias-->
                                    <div class="card shadow mb-4">
                                        <!-- Ajuste informacion del header -->
                                        <div class="card-header py-4">
                                            <h6 class="m-0 font-weight-bold text-primary">Reporte de Asistencias</h6>
                                        </div>
                                        <div class="card-body">
                                            <form id="weekForm">
                                                <div class="container mb-4">
                                                    <div class="row">
                                                           
                                                        <div class="form-group row col-sm">
                                                            <label for="inputEmail3" class="col-sm col-form-label">Seleccione una semana:</label>
                                                            <div class="col-sm">
                                                            <select id="weekSelect" name="weekSelect" class="form-select" onchange="this.form.submit()">
                                                                <?php
                                                                // Generar las opciones de las semanas del año
                                                                for ($i = 1; $i <= 52; $i++) {
                                                                    echo "<option value='$i'" . ($i == $selectedWeek ? ' selected' : '') . ">Semana $i</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            </div>
                                                        </div>
                                                       
                                                        <div class="col-sm d-flex flex-column align-items-center justify-content-center">
                                                            <p class="text-center mb-0">Fecha de inicio:</p>
                                                            <span id="startWeek"><?= $startDate ?></span>
                                                            <input type="hidden" id="startDate" name="startDate" value="<?= $startDate ?>">
                                                        </div>
                                                        <div class="col-sm d-flex flex-column align-items-center justify-content-center">
                                                            <p class="text-center mb-0">Fecha de fin:</p>
                                                            <span id="endWeek"><?= $endDate ?></span>
                                                            <input type="hidden" id="endDate" name="endDate" value="<?= $endDate ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <table id="table_asiss" class="table display" style="width:100%">
                                                <thead id="resultados-thead">
                                                    <tr>
                                                        <th colspan="3">Datos Personales</th>
                                                        <?php
                                                        $asistencia = obtener_asistencia($id_curso, $startDate, $endDate);
                                                        // Recorre las fechas una sola vez y las ordena en orden ascendente
                                                        $fechas_unicas = [];
                                                        foreach ($asistencia as $asis) {
                                                            $date = $asis['fecha_asistencia'];
                                                            if (!in_array($date, $fechas_unicas)) {
                                                                $fechas_unicas[] = $date;
                                                            }
                                                        }
                                                        // Ordena las fechas en orden ascendente
                                                        sort($fechas_unicas);

                                                        // Imprime los encabezados de las fechas, cada una con dos subcolumnas
                                                        foreach ($fechas_unicas as $date) {
                                                            echo "<th colspan='2'>" . $date . "</th>"; // Fecha como encabezado, abarca dos columnas (Estado y Horas tarde)
                                                        }
                                                        ?>
                                                    </tr>
                                                    <tr>
                                                        <th>Id Aprendiz</th>
                                                        <th>Nombre Aprendiz</th>
                                                        <th>Instructor Responsable</th>
                                                        <?php
                                                        // Agrega las sub-columnas para Estado Inasistencia y Horas Tarde debajo de cada fecha
                                                        foreach ($fechas_unicas as $date) {
                                                            echo "<th>Estado Inasistencia</th>";
                                                            echo "<th>Cantidad de Horas tarde</th>";
                                                        }
                                                        ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Crear un array para agrupar por estudiante
                                                    $estudiantes = [];
                                                    foreach ($asistencia as $asis) {
                                                        $user_id = $asis['student_id'];
                                                        if (!isset($estudiantes[$user_id])) {
                                                            $estudiantes[$user_id] = [
                                                                'nombre' => $asis['aprendiz'],
                                                                'instructores' => [],
                                                                'asistencias' => []
                                                            ];
                                                        }

                                                        if (!in_array($asis['instructor'], $estudiantes[$user_id]['instructores'])) {
                                                            $estudiantes[$user_id]['instructores'][] = $asis['instructor'];
                                                        }

                                                        $estudiantes[$user_id]['asistencias'][$asis['instructor']][$asis['fecha_asistencia']] = [
                                                            'estado_inasistencia' => $asis['estado_inasistencia'],
                                                            'horas_tardes' => $asis['horas_tardes']
                                                        ];
                                                    }

                                                    // Imprimir la tabla con los datos agrupados
                                                    foreach ($estudiantes as $user_id => $estudiante) {
                                                        $num_instructores = count($estudiante['instructores']);
                                                        $first_row = true;

                                                        foreach ($estudiante['instructores'] as $instructor) {
                                                            echo "<tr class='group-row' data-student-id='" . $user_id . "'>";

                                                            // Datos del estudiante
                                                            if ($first_row) {
                                                                // Agregamos una clase para identificar las celdas agrupadas
                                                                echo "<td class='student-cell' data-student-id='" . $user_id . "'>" . $user_id . "</td>";
                                                                echo "<td class='student-cell' data-student-id='" . $user_id . "'>" . $estudiante['nombre'] . "</td>";
                                                            } else {
                                                                // Celdas ocultas para mantener la estructura
                                                                echo "<td class='hidden-cell'></td>";
                                                                echo "<td class='hidden-cell'></td>";
                                                            }

                                                            echo "<td>" . $instructor . "</td>";

                                                            // Estados para cada fecha
                                                            foreach ($fechas_unicas as $date) {
                                                                if (isset($estudiante['asistencias'][$instructor][$date])) {
                                                                    $estado_inasis = $estudiante['asistencias'][$instructor][$date]['estado_inasistencia'];
                                                                    $horas_tarde = $estudiante['asistencias'][$instructor][$date]['horas_tardes'];

                                                                    switch ($estado_inasis) {
                                                                        case -1:
                                                                            echo "<td>SUSPENDIDO</td>";
                                                                            break;
                                                                        case 0:
                                                                            echo "<td>NO ASISTIO</td>";
                                                                            break;
                                                                        case 1:
                                                                            echo "<td>ASISTIO</td>";
                                                                            break;
                                                                        case 2:
                                                                            echo "<td>LLEGO TARDE</td>";
                                                                            break;
                                                                        case 3:
                                                                            echo "<td>EXCUSA MEDICA</td>";
                                                                            break;
                                                                    }
                                                                    echo "<td>" . $horas_tarde . "</td>";
                                                                } else {
                                                                    echo "<td></td><td></td>";
                                                                }
                                                            }

                                                            echo "</tr>";
                                                            $first_row = false;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <!-- llamada Footer -->
<?php include '../footer.php';
        } catch (PDOException $e) {
            // Captura de la excepción en caso de que el usuario no esté matriculado
            echo "Usuario no matriculado en curso: " . $e->getMessage() . "\n";
            log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
            echo "<script>
                    window.location.href = '/dashboard-lms/error/error_ingre.php';
                </script>";
        }
    } else {
        // Si no hay usuario autenticado o la sesión es inválida, se lanza una excepción
        throw new PDOException("ERROR EN LA SESION.");
    }
} catch (PDOException $e) {
    // Captura de la excepción en caso de un error general en la sesión
    echo "Error en la sesión: " . $e->getMessage() . "\n";
    log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
    echo "<script>
            window.location.href = '/dashboard-lms/error/error_acti.php';
        </script>";
}
?>