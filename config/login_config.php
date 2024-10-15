<?php
require '../vendor/autoload.php';
// Incluir el archivo de configuración de Zajuna y el controlador de sesiones
require_once 'db_config.php';
require_once '../controllers/session_controller.php';

try {
    // Obtener los datos del usuario y el código del curso desde el bloque de calificaciones de Zajuna
    $user_id = base64_decode($_GET['user']);
    $id_url_curso = base64_decode($_GET['idnumber']);
    $tipo = base64_decode($_GET['roleid']);
    $encrypted_curso_id = base64_encode($id_url_curso);


    // Preparar la consulta para enviar los parámetros a la base de datos y filtrar la información
    $query = $conn->prepare("SELECT obtenerSessionRA(:id_url_curso, :user_id, :tipo)");
    $query->bindParam(':id_url_curso', $id_url_curso, PDO::PARAM_INT);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $query->bindParam(':tipo', $tipo, PDO::PARAM_INT);
    $query->execute();
    $sesion_query = "SELECT * FROM vista_ses";
    $query = $conn->prepare($sesion_query);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_OBJ);

    // // Configuración de la sesión segura
    // ini_set('session.cookie_httponly', 1);  // Evita el acceso a cookies a través de JavaScript
    // ini_set('session.cookie_secure', 1);    // Asegu // // Configuración de la sesión segura
    // ini_set('session.cookie_httponly', 1);  // Evita el acceso a cookies a través de JavaScript
    // ini_set('session.cookie_secure', 1);    // Asegura que las cookies solo se envíen a través de HTTPS
    // ini_set('session.use_strict_mode', 1);  // Previene que PHP acepte un ID de sesión no válido
    // ini_set('session.use_only_cookies', 1); // Deshabilita el uso de sesiones basadas en URL
    // ini_set('session.cookie_samesite', 'Strict'); // Previene el envío de cookies en solicitudes de tercerosra que las cookies solo se envíen a través de HTTPS
    // ini_set('session.use_strict_mode', 1);  // Previene que PHP acepte un ID de sesión no válido
    // ini_set('session.use_only_cookies', 1); // Deshabilita el uso de sesiones basadas en URL
    // ini_set('session.cookie_samesite', 'Strict'); // Previene el envío de cookies en solicitudes de terceros
    session_start();

    // Verificar la expiración de la sesión por inactividad
    if (!checkSessionTimeout()) {
        header("Location: /zajuna/");
        exit();
    }

    // Verificar la dirección IP
    if (!isset($_SESSION['IP_ADDRESS'])) {
        $_SESSION['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
    } elseif ($_SESSION['IP_ADDRESS'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header("Location: /zajuna/");
        exit();
    }

    // Verificar el User Agent
    if (!isset($_SESSION['USER_AGENT'])) {
        $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    } elseif ($_SESSION['USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header("Location: /zajuna/");
        exit();
    }

    // Si la base de datos devuelve información para el usuario logueado, iniciar la sesión
    if ($user) {
        session_regenerate_id(true); // Regenerar ID de sesión para prevenir ataques de fijación de sesión
        $_SESSION['user'] = $user;
        $user_tipo = $user->tipo_user;

        header("Location: ../views/competencias.php?idnumber=$encrypted_curso_id");
        exit();
    } else {
        // Si la consulta no encuentra información o la conexión falla en la BD, redireccionar al usuario
        header("Location: /zajuna/");
        exit();
    }
} catch (PDOException $e) {
    // Manejo de errores de base de datos
    error_log("Error en la base de datos: " . $e->getMessage());
    header("Location: /lms-califica/error/error_conexion.php");
    exit();
} catch (Exception $e) {
    // Manejo de otros errores
    error_log("Ocurrió un error: " . $e->getMessage());
    header("Location: /lms-califica/error/error_conexion.php");
    exit();
}
