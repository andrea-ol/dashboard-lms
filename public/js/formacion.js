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

$(document).ready(function () {
  $('#consultarBtn').on('click', function () {
    var id_curso = $('#cursoSelect').val();
    var fechaInicio = $('#fechaInicio').val();
    var fechaFin = $('#fechaFin').val();

    if (id_curso && fechaInicio && fechaFin) {
      $.ajax({
        url: 'controllers/consultas_controller.php',
        type: 'POST',
        data: {
          id_curso: id_curso,
          fechaInicio: fechaInicio,
          fechaFin: fechaFin
        },
        success: function (response) {
          console.log('Respuesta del servidor:', response);
          // Aquí puedes manejar la respuesta
        },
        error: function (xhr, status, error) {
          console.error('Error en la solicitud:', error);
        }
      });
    } else {
      alert('Por favor, complete todos los campos.');
    }
  });
});




