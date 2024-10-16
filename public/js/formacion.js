/* Funciòn de redirecciòn la pagina de resultadoap -> compotencias */
function redirectToCategorias(centroFormacion) {
  window.location.href = `./categorias.php?C=${centroFormacion}`;
}

/* Función de redirección a la página de Competencias -> resultadoap */
function redirectCursos(centroFormacion, tipoFormacion) {
  window.location.href = `../views/cursos.php?C=${centroFormacion}&F=${tipoFormacion}`;
}

const ctx = document.getElementById('myChart');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
    datasets: [{
      label: '# of Votes',
      data: [12, 19, 3, 5, 2, 3],
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





