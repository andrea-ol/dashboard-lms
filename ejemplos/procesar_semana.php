<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $semana = $_POST['weekSelect'];
    $fechaInicio = $_POST['startDate'];
    $fechaFin = $_POST['endDate'];

    echo "Semana seleccionada: $semana<br>";
    echo "Fecha de inicio: $fechaInicio<br>";
    echo "Fecha de fin: $fechaFin<br>";
}
?>
