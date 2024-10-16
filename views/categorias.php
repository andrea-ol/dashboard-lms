<?php
// Importar el controlador de sesiones para gestionar las sesiones de usuario
require_once '../controllers/session_controller.php';
require_once '../vendor/autoload.php';
// Incluir configuración de base de datos y otros recursos necesarios
require_once '../config/db_config.php';
require_once '../config/sofia_config.php';
// Incluir el controlador necesario para gestionar las categorias
require_once '../controllers/category_controller.php';

session_start(); // Iniciar la sesión
// Incluir el header de la página
include '../header.php';

// Intentar ejecutar el bloque de código y capturar posibles errores relacionados con la sesión
try {
    // Verificar si el usuario está autenticado y si la sesión no ha caducado
    if (isset($_SESSION['user']) && checkSessionTimeout()) {
        $centroFormacion = $_GET['C'];
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
                                        <div tabindex="0" role="button" aria-label="Regresar al Curso" onclick="redirectToZajuna()" onkeydown="if(event.key === 'Enter' || event.key === ' ') { redirectToZajuna(); }">
                                            <img src="../public/assets/img/icno-de-regresar.svg" alt="Ícono de regresar" style="margin-right: 5px;">
                                            <u id="titulo-regresar">Regresar a Zajuna</u>
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
                            <!-- Ajuste cards categorias -->
                            <div class="row">
                                <?php
                                // Recuperar las categorias asociadas al curso
                                $formacion = obtenerFormacion();
                                foreach ($formacion as $categorias) {
                                    $tipoFormacion = $categorias['nombre'];

                                ?>
                                    <div class="col-sm-4 col-md-6 col-lg-4 mb-3">
                                        <!-- Tarjeta para cada competencia -->
                                        <div class="card h-100 d-flex flex-column" id="index-card">
                                            <div class="card-body flex-grow-1">
                                                <!-- Muestra el ID y nombre de la competencia -->
                                                <h5 class="card-title d-flex align-items-center flex-wrap">
                                                    <img src="../public/assets/img/boton_competencias.svg" alt="Ícono de regresar"
                                                        class="img-fluid me-2" style="max-width: 80px; height: auto;">
                                                    <span class="text-success fw-bold">Tipo de Formación:</span>
                                                </h5>
                                                <p class="card-text text-start">
                                                    <!-- Se muestra una versión truncada del nombre de la competencia y se proporciona una opción para leer más -->
                                                    <span class="competencia-text">
                                                        <?= ucfirst(mb_strtolower($tipoFormacion)); ?>
                                                    </span>
                                                </p>
                                                <hr class="my-3 text-success" />
                                                <div class="mt-auto">
                                                    <!-- Botón para redirigir a los resultados de aprendizaje de la competencia -->
                                                    <button type="button" class="btn btn-success w-100 lang_sign"
                                                        onclick="redirectCursos('<?= $centroFormacion; ?>', '<?= $tipoFormacion; ?>')">
                                                        Ver cursos
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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