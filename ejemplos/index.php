<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selector de Semanas</title>
</head>
<body>
    <h1>Seleccionar Semana</h1>
    <form id="weekForm" method="POST" action="procesar_semana.php">
        <label for="weekSelect">Seleccione una semana:</label>
        <select id="weekSelect" name="weekSelect" onchange="updateWeekRange()">
            <!-- Opciones de semanas -->
        </select>
        
        <!-- Campos ocultos para almacenar las fechas -->
        <input type="hidden" id="startDate" name="startDate">
        <input type="hidden" id="endDate" name="endDate">

        <p>Fecha de inicio: <span id="startWeek"></span></p>
        <p>Fecha de fin: <span id="endWeek"></span></p>
        
        <button type="submit">Enviar</button>
    </form>

    <script>
        // Función para generar las opciones del select con las semanas
        function generateWeekOptions() {
            const weekSelect = document.getElementById("weekSelect");
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            
            // Iterar por 52 semanas (aproximadamente un año)
            for (let i = 1; i <= 52; i++) {
                const option = document.createElement("option");
                option.value = i;
                option.text = "Semana " + i;
                weekSelect.appendChild(option);
            }
        }

        // Función para calcular las fechas de inicio y fin de una semana
        function getWeekStartEnd(weekNumber) {
            const startOfYear = new Date(new Date().getFullYear(), 0, 1);
            const daysOffset = (weekNumber - 1) * 7;

            const startDate = new Date(startOfYear);
            startDate.setDate(startOfYear.getDate() + daysOffset);

            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);

            return {
                startDate: startDate.toISOString().split('T')[0], // Formato YYYY-MM-DD
                endDate: endDate.toISOString().split('T')[0]
            };
        }

        // Función para actualizar los campos de fechas
        function updateWeekRange() {
            const weekNumber = document.getElementById("weekSelect").value;
            const weekRange = getWeekStartEnd(weekNumber);

            // Mostrar las fechas en el HTML
            document.getElementById("startWeek").textContent = weekRange.startDate;
            document.getElementById("endWeek").textContent = weekRange.endDate;

            // Actualizar los campos ocultos para enviar al backend
            document.getElementById("startDate").value = weekRange.startDate;
            document.getElementById("endDate").value = weekRange.endDate;
        }

        // Llamada inicial para llenar las opciones de semanas
        window.onload = generateWeekOptions;
    </script>
</body>
</html>
