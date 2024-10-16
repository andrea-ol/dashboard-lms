<?php
// Importar el controlador de sesiones para gestionar las sesiones de usuario
require_once '../controllers/session_controller.php';
require_once '../vendor/autoload.php';
// Incluir configuración de base de datos y otros recursos necesarios
require_once '../config/db_config.php';
require_once '../config/sofia_config.php';
// Incluir el controlador necesario para gestionar las categorias
require_once '../controllers/cursos_controller.php';


session_start(); // Iniciar la sesión
// Incluir el header de la página
include '../header.php';

// Intentar ejecutar el bloque de código y capturar posibles errores relacionados con la sesión
try {
    // Verificar si el usuario está autenticado y si la sesión no ha caducado
    if (isset($_SESSION['user']) && checkSessionTimeout()) {

        $centroFormacion = $_GET['C'];
        $tipoFormacion = $_GET['F'];

        // Intentar validar la pertenencia del usuario al curso y capturar posibles errores
        try {
            // Si el usuario pertenece al curso, se muestra la vista correspondiente
?>
            <main>
                <!-- Título principal de la página -->
                <h5 class="p-2 text-center bg-primary text-white">Centro de Información</h5>

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
                                        <div tabindex="0" role="button" aria-label="Regresar a Categorias" onclick="redirectToCategorias('<?= $centroFormacion; ?>')" onkeydown="if(event.key === 'Enter' || event.key === ' ') { redirectToCategorias('<?= $centroFormacion; ?>'); }">
                                            <img src="../public/assets/img/icno-de-regresar.svg" alt="Ícono de regresar" style="margin-right: 5px;">
                                            <u id="titulo-regresar">Regresar a Categorias</u>
                                        </div>
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <!-- Boton de regresar a la pagina anterior (ZAJUNA) -->
                        <div class="card m-4">
                            <ol class="breadcrumb m-2">
                                <!-- TITULO CON BIENVENIDA AL USUARIO INGRESADO  -->
                                <li class="m-2"><strong>Bienvenido/a</strong> <?php echo mb_strtoupper($user->firstname . ' ' . $user->lastname, 'UTF-8'); ?>
                                </li>
                            </ol>

                            <!-- Ajuste cards charts -->
                            <div class="row">
                                <!-- Default dropright button -->
                                <div>
                                    <form id="cursoForm">

                                        <div class="form-group row">
                                            <label for="staticEmail" class="col-sm-2 col-form-label">Curso</label>
                                            <div class="col-sm-10">
                                                <select id="cursoSelect" class="form-select" onchange="mostrarInputs(this.value)">
                                                    <option value="">Seleccione un curso</option>
                                                    <?php
                                                    $cursos = obtenerCursos($centroFormacion, $tipoFormacion);

                                                    if (!empty($cursos)) {
                                                        foreach ($cursos as $curso) {
                                                            $id_curso = $curso['id'];
                                                            echo '<option value="' . $id_curso . '">' . htmlspecialchars($curso['fullname']) . '</option>';
                                                        }
                                                    } else {
                                                        echo '<option value="">No se encontraron cursos</option>';
                                                    }


                                                    
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row" id="inputsTime" style="display: none;">
                                            <div class="form-group col-md-4">
                                                <label for="fechaInicio" class="col-sm-2 col-form-label">Fecha de inicio:</label>
                                                <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="fechaFin" class="col-sm-2 col-form-label">Fecha de fin:</label>
                                                <input type="date" class="form-control" id="fechaFin" name="fechaFin">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <button class="btn btn-success mt-2" type="button" id="consultarBtn">Consultar</button>
                                            </div>
                                        </div>
                                        <!-- Inputs de tipo time que se mostrarán después de seleccionar un curso -->
                                    </form>
                                </div>
                            </div>
                            <!-- Control de asistencias -->
                            <div class="row" id="cardschart" style="display: none;">

                                <div class="col-xl-10 col-lg-8">
                                    <!-- Area Chart -->
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-4">
                                            <h6 class="m-0 font-weight-bold text-primary">Control de asistencias</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-area">
                                                <canvas id="myChart"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bar Chart -->
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Bar Chart</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-bar">
                                                <canvas id="myBarChart"></canvas>
                                            </div>

                                        </div>
                                    </div>

                                </div>

                                <!-- Donut Chart -->
                                <div class="col-xl-4 col-lg-5">
                                    <div class="card shadow mb-4">
                                        <!-- Card Header - Dropdown -->
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Donut Chart</h6>
                                        </div>
                                        <!-- Card Body -->
                                        <div class="card-body">
                                            <div class="chart-pie pt-4">
                                                <canvas id="myPieChart"></canvas>
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