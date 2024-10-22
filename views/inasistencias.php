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

// Intentar ejecutar el bloque de código y capturar posibles errores relacionados con la sesión
try {
    // Verificar si el usuario está autenticado y si la sesión no ha caducado
    if (isset($_SESSION['user']) && checkSessionTimeout()) {
        // Verificar si la solicitud es POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_curso = $_POST['fic_id'];
            print_r($id_curso);
        }
        // Intentar validar la pertenencia del usuario al curso y capturar posibles errores
        try {
            // Si el usuario pertenece al curso, se muestra la vista correspondiente
            ?>
            <main>
                <!-- Título principal de la página -->
                <h5 class="p-2 text-center bg-primary text-white">Detalle de Inasistencias</h5>

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
                                            <table id="table_asiss" class="display" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>1</th>
                                                            <th>2</th>
                                                            <th>3</th>
                                                            <th>4</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                2
                                                            </td>
                                                            <td>
                                                                2
                                                            </td>
                                                            <td>
                                                                2
                                                            </td>
                                                            <td>
                                                                2
                                                            </td>
                                                        </tr>
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