<?php
use Dotenv\Dotenv;
// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load(); 

/* Configuración de la conexión a la Base de Datos utilizando las variables de entorno */
$rutaServidor = $_ENV['SOFIA_HOST'];
$puerto = $_ENV['SOFIA_PORT'];
$usuario = $_ENV['SOFIA_USER'];
$password = $_ENV['SOFIA_PASSWORD'];
$nombreBaseDeDatos = $_ENV['SOFIA_BD'];

/* Validación de la conexión */
try {
    /* Validación de la conexión */
    $replica = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $password);
    $replica->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    /* PDO es un controlador que implementa la interfaz de Objetos de Datos de PHP (PDO), permitir el acceso desde PHP a bases de datos de PostgreSQL */
} catch (PDOException $e) {

    echo "Error al conectarse a la base de datos: " . $e->getMessage() . "\n";
    echo "<script>
            window.location.href = '/lmsActividades/error/error_conexion.php';
        </script>";
}