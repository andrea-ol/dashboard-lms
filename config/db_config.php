<?php
require '../vendor/autoload.php';
// Use para aplicar Dotenv variables de entorno
use Dotenv\Dotenv;
// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

/* Configuración de la conexión a la Base de Datos utilizando las variables de entorno */
$rutaServidor = $_ENV['ZAJUNA_HOST'];
$puerto = $_ENV['ZAJUNA_PORT'];
$usuario = $_ENV['ZAJUNA_USER'];
$password = $_ENV['ZAJUNA_PASSWORD'];
$nombreBaseDeDatos = $_ENV['ZAJUNA_BD'];

/* Validación de la conexión */
try {
    $conn = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Error conexion a base de datos: " . $e->getMessage() . "\n";
    echo "<script>
    window.location.href = '/dashboard-lms/error/error_conexion.php';
</script>";
}
