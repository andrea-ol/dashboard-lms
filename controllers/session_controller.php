<?php

function checkSessionTimeout()
{
    $timeout_duration = 3600; // Duración del tiempo de espera en segundos (60 segundos = 1 minuto)

    // Obtener la URL actual
    $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // TENER PRESENTE PARA SESION USANDO PUERTO SEGURO
    // $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // Verificar si la URL actual pertenece a 'localhost/lms-califica/'
    if (strpos($current_url, '/lms-califica/') !== false) {
        // Verificar si el tiempo de inactividad ha excedido el límite
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];

            if ($elapsed_time > $timeout_duration) {
                // La última solicitud fue hace más de $timeout_duration segundos
                session_unset();     // unset $_SESSION variable for the run-time 
                session_destroy();   // destroy session data in storage
                echo "<script>
                localStorage.clear();
                window.location.href = '/lms-califica/error/error.php';
                </script>";
                return false; // Indica que la sesión ha expirado
            }
        }

        // Si no ha excedido el tiempo de espera, refrescar la sesión
        $_SESSION['LAST_ACTIVITY'] = time(); // Actualiza la marca de tiempo de la última actividad
        return true; // Indica que la sesión sigue activa
    } else {
        // Si la URL no pertenece a 'localhost/lms-califica/', evaluar el tiempo de inactividad
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];

            if ($elapsed_time > $timeout_duration) {
                // La última solicitud fue hace más de $timeout_duration segundos
                session_unset();     // unset $_SESSION variable for the run-time 
                session_destroy();   // destroy session data in storage
                echo "<script>
                localStorage.clear();
                window.location.href = '/lms-califica/error/error.php';
                </script>";
                return false; // Indica que la sesión ha expirado
            }
        }
    }

    $_SESSION['LAST_ACTIVITY'] = time(); // Actualiza la marca de tiempo de la última actividad si estamos en otra ruta
    return true; // Indica que la sesión sigue activa
}
