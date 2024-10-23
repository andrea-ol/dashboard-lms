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
        $asistencia = obtener_asistencia($id_curso);
        // Intentar validar la pertenencia del usuario al curso y capturar posibles errores
        try {
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
                                <div class="col-xl-10 col-lg-8">
                                    <!-- Area Chart -->
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-4">
                                            <h6 class="m-0 font-weight-bold text-primary">Reporte de Asistencias</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-area">
                                                <table id="table_asiss" class="table display" style="width:100%">
                                                    <thead class="thead_resultados">
                                                        <tr>
                                                            <th colspan="3">Datos Personales</th>
                                                            <?php
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
                                                        // Agrupa por estudiante utilizando su ID
                                                        $aprendices = [];

                                                        // Recorre el array de asistencia para agrupar por estudiante
                                                        foreach ($asistencia as $asis) {
                                                            $user_id = $asis['student_id'];
                                                            $aprendices[$user_id]['aprendiz'] = $asis['aprendiz']; // Nombre del aprendiz
                                                            $aprendices[$user_id]['instructores'][] = [
                                                                'instructor' => $asis['instructor'],
                                                                'fecha_asistencia' => $asis['fecha_asistencia'],
                                                                'estado_inasistencia' => $asis['estado_inasistencia'],
                                                                'horas_tardes' => $asis['horas_tardes']
                                                            ];
                                                        }

                                                        // Imprimir la tabla agrupada por aprendiz
                                                        foreach ($aprendices as $user_id => $data) {
                                                            $user_name = $data['aprendiz'];

                                                            // Imprime una nueva fila por cada aprendiz
                                                            echo "<tr>";
                                                            echo "<td>" . $user_id . "</td>"; // Id del Aprendiz (una sola vez)
                                                            echo "<td>" . $user_name . "</td>"; // Nombre del Aprendiz (una sola vez)

                                                            // Ahora imprime los instructores y los datos de asistencia
                                                            $instructors_printed = false;
                                                            foreach ($data['instructores'] as $instructor_data) {
                                                                if ($instructors_printed) {
                                                                    // Si ya imprimimos los datos del aprendiz, saltamos a la columna del instructor en adelante
                                                                    echo "<tr><td></td><td></td>";
                                                                }

                                                                // Imprime el nombre del instructor responsable
                                                                echo "<td>" . $instructor_data['instructor'] . "</td>";

                                                                // Imprime los datos correspondientes a cada fecha
                                                                foreach ($fechas_unicas as $date) {
                                                                    if ($instructor_data['fecha_asistencia'] == $date) {
                                                                        $estado_inasis = $instructor_data['estado_inasistencia'];
                                                                        $horas_tarde = $instructor_data['horas_tardes'];

                                                                        // Estado Calificación en texto
                                                                        $state1 = "SUSPENDIDO";
                                                                        $state2 = "NO ASISTIO";
                                                                        $state3 = "ASISTIO";
                                                                        $state4 = "LLEGO TARDE";
                                                                        $state5 = "EXCUSA MEDICA";

                                                                        if ($estado_inasis == -1) {
                                                                            echo "<td>" . $state1 . "</td>";
                                                                        } else if ($estado_inasis == 0) {
                                                                            echo "<td>" . $state2 . "</td>";
                                                                        } else if ($estado_inasis == 1) {
                                                                            echo "<td>" . $state3 . "</td>";
                                                                        } else if ($estado_inasis == 2) {
                                                                            echo "<td>" . $state4 . "</td>";
                                                                        } else if ($estado_inasis == 3) {
                                                                            echo "<td>" . $state5 . "</td>";
                                                                        }

                                                                        echo "<td>" . $horas_tarde . "</td>";
                                                                    } else {
                                                                        echo "<td></td><td></td>"; // Deja las celdas vacías si no hay coincidencia de fecha
                                                                    }
                                                                }

                                                                echo "</tr>"; // Cierra la fila de este instructor para el aprendiz actual
                                                                $instructors_printed = true;
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