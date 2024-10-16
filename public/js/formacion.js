/* Funciòn de redirecciòn la pagina de resultadoap -> compotencias */
function redirectToCategorias(centroFormacion) {
  window.location.href = `./categorias.php?C=${centroFormacion}`;
}

/* Función de redirección a la página de Competencias -> resultadoap */
function redirectCursos(centroFormacion, tipoFormacion) {
  window.location.href = `../views/cursos.php?C=${centroFormacion}&F=${tipoFormacion}`;
}

const ctx = document.getElementById('myChart');

function mostrarInputs(cursoId) {
  var inputsTime = document.getElementById('inputsTime');
  if (cursoId) {
    inputsTime.style.display = 'block';
  } else {
    inputsTime.style.display = 'none';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('consultarBtn').addEventListener('click', function () {
    var id_curso = document.getElementById('cursoSelect').value;
    var fechaInicio = document.getElementById('fechaInicio').value;
    var fechaFin = document.getElementById('fechaFin').value;

    console.log(id_curso);
    console.log(fechaInicio);
    console.log(fechaFin);

    if (id_curso && fechaInicio && fechaFin) {
      // Configuración de los datos para enviar
      var data = new URLSearchParams();
      data.append('id_curso', id_curso);
      data.append('fechaInicio', fechaInicio);
      data.append('fechaFin', fechaFin);

      // Realizar la solicitud con fetch
      fetch('/dashboard-lms/controllers/consultas_controller.php', {
        method: 'POST',
        body: data,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok ' + response.statusText);
          }
          return response.json(); // Asumiendo que el servidor devuelve JSON
        })
        .then(data => {
          console.log('Respuesta del servidor:', data);

          if (data.success) {
            // Extraer los valores de count de cada parámetro
            const excusaMedicaCount = data.data.excusaMedica[0].count;
            const llegadaTardeCount = data.data.llegadaTarde[0].count;
            const asistenciaCount = data.data.asistencia[0].count;
            const inasistenciaCount = data.data.inasistencia[0].count;
            const suspendidoCount = data.data.suspendido[0].count;
    
            // Crear la gráfica con los valores obtenidos
            const ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Excusa Médica', 'Llegada Tarde', 'Asistencia', 'Inasistencia', 'Suspendido'],
                    datasets: [{
                        label: 'Conteo de Asistencia',
                        data: [excusaMedicaCount, llegadaTardeCount, asistenciaCount, inasistenciaCount, suspendidoCount],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',   
                            'rgba(54, 162, 235, 0.2)',   
                            'rgba(75, 192, 192, 0.2)',   
                            'rgba(153, 102, 255, 0.2)',  
                            'rgba(255, 159, 64, 0.2)'    
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            console.error('Error en la respuesta del servidor:', data.message);
        }

          // Aquí puedes manejar la respuesta
        })
        .catch(error => {
          console.error('Error en la solicitud:', error);
        });
    } else {
      alert('Por favor, complete todos los campos.');
    }
  });
});





