<?php
// Importar el controlador de sesiones para gestionar las sesiones de usuario
require_once '../controllers/session_controller.php';
require_once '../vendor/autoload.php';
// Incluir configuración de base de datos y otros recursos necesarios
require_once '../config/db_config.php';
require_once '../config/sofia_config.php';
// Incluir el controlador necesario para gestionar las competencias
require_once '../controllers/comp_controller.php';

session_start(); // Iniciar la sesión
// Incluir el header de la página
include '../header.php';

// Intentar ejecutar el bloque de código y capturar posibles errores relacionados con la sesión
try {
    // Verificar si el usuario está autenticado y si la sesión no ha caducado
    if (isset($_SESSION['user']) && checkSessionTimeout()) {
        // Obtener el objeto de usuario de la sesión
        $user = $_SESSION['user'];
        $user_id = $user->userid; // Obtener el ID del usuario

        // Decodificar el ID del curso obtenido por GET para obtener su valor original
        $curso = base64_decode($_GET['idnumber']);

        // Obtener los nombres relacionados con el curso a partir de la función 'nombre_ficha'
        $names = nombre_ficha($curso);
        foreach ($names as $name) {
            // Formatear el nombre de la ficha y asignar otros valores relevantes
            $nombre_ficha = ucfirst(mb_strtolower($name->fullname));
            $id_curso = $name->id; // ID del curso
            $id_number = $name->idnumber; // Número identificador del curso
        }

        // Intentar validar la pertenencia del usuario al curso y capturar posibles errores
        try {
            // Obtener la lista de ingresos para el curso específico y el usuario en cuestión
            $ingre = ingreso($id_curso, $user_id);
            $encontrado = false; // Bandera para determinar si el usuario pertenece al curso

            // Recorrer la lista de ingresos para verificar si el usuario está inscrito en el curso
            foreach ($ingre as $ingr) {
                $tipo_user = $ingr['shortname']; // Tipo de usuario
                if ($ingr['id'] == $user_id) {
                    $encontrado = true; // Usuario encontrado en el curso
                    break;
                }
            }
            // Si el usuario pertenece al curso, se muestra la vista correspondiente
            if ($encontrado) {
?>

                <main>
                    <!-- Título principal de la página -->
                    <h5 class="p-2 text-center bg-primary text-white">Centro de Resultados</h5>

                    <!-- Contenedor para el historial de navegación -->
                    <div class="history-container my-2">
                        <?php
                        // Función para mostrar el historial de navegación en cada página
                        mostrar_historial('lms-califica');
                        ?>
                    </div>

                    <div class="container-fluid px-4 my-4">
                        <div class="card p-2 p-md-5">
                            <!-- Encabezado de la tarjeta principal -->
                            <div class="container-fluid container-hearder">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <!-- Botón para regresar al curso anterior -->
                                        <h6 class="flex-wrap lang_sign">
                                            <div tabindex="0" role="button" aria-label="Regresar al Curso" onclick="redirectToZajuna('<?= $id_curso; ?>')" onkeydown="if(event.key === 'Enter' || event.key === ' ') { redirectToZajuna('<?= $id_curso; ?>'); }">
                                                <img src="../public/assets/img/icno-de-regresar.svg" alt="Ícono de regresar" style="margin-right: 5px;">
                                                <u id="titulo-regresar">Regresar al Curso</u>
                                            </div>
                                        </h6>
                                    </div>
                                    <div class="col-sm-7 d-flex">
                                        <!-- Muestra el nombre de la ficha del curso -->
                                        <h3 style="color: white;" class="my-2 text-start">
                                            Nombre:<span data-toggle="tooltip" data-placement="top" title="<?php echo $nombre_ficha; ?>" id="titulo_nombre">
                                                <?php
                                                // Si el nombre es muy largo, se trunca y se añaden puntos suspensivos
                                                if (strlen($nombre_ficha) > 64) {
                                                    echo substr($nombre_ficha, 0, 64) . '...';
                                                } else {
                                                    echo $nombre_ficha;
                                                }
                                                ?>
                                            </span>
                                        </h3>
                                    </div>
                                    <div class="col-sm-3 d-flex">
                                        <!-- Muestra el número identificador de la ficha -->
                                        <h3 style="color: white;" class="my-2 text-start">
                                            <img id="titulo-img" src="../public/assets/img/documento.svg" alt="?">
                                            Ficha: <span id="titulo_ficha"> <?php echo htmlspecialchars($id_number); ?></span>
                                        </h3>
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
                                <!-- Ajuste cards competencias -->
                                <div class="row">
                                    <?php
                                    // Recuperar las competencias asociadas al curso
                                    $courses = obtenerCompetenciasPorCurso($curso);
                                    foreach ($courses as $course) {
                                        // Encriptar los IDs de la ficha y la competencia para su uso en los enlaces
                                        $codigo_ficha = $course->FIC_ID;
                                        $encoded_ficha = base64_encode($codigo_ficha);
                                        $id_competencia = $course->CMP_ID;
                                        $encoded_competencia = base64_encode($id_competencia);
                                        // Formatear el nombre de la competencia y limitar el texto si es necesario
                                        $firstname = $course->CMP_NOMBRE;

                                        // Convertimos la cadena a minúsculas y luego aplicamos ucfirst para capitalizar la primera letra
                                        $processed_text = ucfirst(mb_strtolower($firstname));

                                        // Verificamos la longitud de la cadena
                                        if (mb_strlen($processed_text) > 100) {
                                            // Si excede el límite, aplicamos el truncamiento con 'mb_strimwidth'
                                            $limited_text = mb_strimwidth($processed_text, 0, 100, '...');
                                            $show_read_more = true;  // Mostramos el enlace "Leer más"
                                        } else {
                                            // Si no excede, simplemente lo dejamos como está
                                            $limited_text = $processed_text;
                                            $show_read_more = false;  // No mostramos el enlace "Leer más"
                                        }
                                    ?>
                                        <div class="col-sm-4 col-md-6 col-lg-4 mb-3">
                                            <!-- Tarjeta para cada competencia -->
                                            <div class="card h-100 d-flex flex-column" id="index-card">
                                                <div class="card-body flex-grow-1">
                                                    <!-- Muestra el ID y nombre de la competencia -->
                                                    <h5 class="card-title d-flex align-items-center flex-wrap">
                                                        <img src="../public/assets/img/boton_competencias.svg" alt="Ícono de regresar"
                                                            class="img-fluid me-2" style="max-width: 80px; height: auto;">
                                                        <span class="text-success fw-bold">ID Competencia:</span>
                                                        <span class="text-break ms-1"><?= $id_competencia; ?></span>
                                                    </h5>
                                                    <p class="card-text text-start">
                                                        <span class="text-success fw-bold">Nombre Competencia: </span>
                                                        <!-- Se muestra una versión truncada del nombre de la competencia y se proporciona una opción para leer más -->
                                                        <span class="competencia-text"
                                                            data-full-text="<?= ucfirst(mb_strtolower($firstname)); ?>"
                                                            data-limited-text="<?= $limited_text; ?>">
                                                            <?= $limited_text; ?>
                                                        </span>
                                                        <?php if ($show_read_more): ?>
                                                            <a href="#" class="read-more-comp text-success lang_sign">Leer más</a>
                                                        <?php endif; ?>
                                                    </p>

                                                    <hr class="my-3 text-success" />
                                                    <div class="mt-auto">
                                                        <!-- Botón para redirigir a los resultados de aprendizaje de la competencia -->
                                                        <button type="button" class="btn btn-success w-100 lang_sign"
                                                            onclick="redirectComToResultados('<?= $encoded_ficha; ?>', '<?= $encoded_competencia; ?>')">
                                                            Resultados de Aprendizaje
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <!-- Fin del ajuste cards competencias -->
                            </div>
                        </div>
                    </div>
                </main>
                <!-- llamada Footer -->
<?php include '../footer.php';
            } else {
                // Si el usuario no está matriculado en el curso, se lanza una excepción
                throw new PDOException("Usuario no matriculado en curso.");
            }
        } catch (PDOException $e) {
            // Captura de la excepción en caso de que el usuario no esté matriculado
            echo "Usuario no matriculado en curso: " . $e->getMessage() . "\n";
            log_error($replica, get_class($e), $e->getCode(), $e->getMessage());
            echo "<script>
                    window.location.href = '/lms-califica/error/error_ingre.php';
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
            window.location.href = '/lms-califica/error/error_acti.php';
        </script>";
}
?>