/* Funciòn de redirecciòn la pagina de resultadoap -> compotencias */
function redirectToCategorias(centroFormacion) {
  window.location.href = `./categorias.php?C=${centroFormacion}`;
}

/* Función de redirección a la página de Competencias -> resultadoap */
function redirectCursos(centroFormacion, tipoFormacion) {
  window.location.href = `../views/cursos.php?C=${centroFormacion}&F=${tipoFormacion}`;
}

const ctx = document.getElementById("myChart");

function mostrarInputs() {
  var inputsTime = document.getElementById("inputsTime");
  if (inputsTime) {
    inputsTime.style.display = "block";
  } else {
    inputsTime.style.display = "none";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("consultarBtn")
    .addEventListener("click", function () {
      var select = document.getElementById("cursoSelect");
      var selectedOption = select.options[select.selectedIndex];
      // Obtener el value de la opción seleccionada
      var id_curso = selectedOption.value;
      // Obtener los atributos de la opción seleccionada
      var fecha = selectedOption.getAttribute("data-fecha");
      var categoria = selectedOption.getAttribute("data-categoria");
      var idnumber = selectedOption.getAttribute("data-number");

      var fechaInicio = document.getElementById("fechaInicio").value;
      var fechaFin = document.getElementById("fechaFin").value;

      var cardschart = document.getElementById("cardschart");
      if (id_curso) {
        cardschart.style.display = "block";
      } else {
        cardschart.style.display = "none";
      }

      if (id_curso && fechaInicio && fechaFin) {
        // Configuración de los datos para enviar
        var data = new URLSearchParams();
        data.append("id_curso", id_curso);
        data.append("fechaInicio", fechaInicio);
        data.append("fechaFin", fechaFin);
        data.append("fecha", fecha);
        data.append("categoria", categoria);
        data.append("idnumber", idnumber);

        // Realizar la solicitud con fetch
        fetch("/dashboard-lms/controllers/consultas_controller.php", {
          method: "POST",
          body: data,
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(
                "Network response was not ok " + response.statusText
              );
            }
            return response.json(); // Asumiendo que el servidor devuelve JSON
          })
          .then((data) => {
            console.log("Respuesta del servidor:", data);

            if (data.success) {
              // Extraer los valores de count de cada parámetro
              const excusaMedicaCount = data.data.excusaMedica[0]?.count || 0;
              const llegadaTardeCount = data.data.llegadaTarde[0]?.count || 0;
              const asistenciaCount = data.data.asistencia[0]?.count || 0;
              const inasistenciaCount = data.data.inasistencia[0]?.count || 0;
              const suspendidoCount = data.data.suspendido[0]?.count || 0;
              // Extraer los valores de count de cada parámetro para totales de aprendices
              const total_suspendidos = data.data.suspendido[0].count;
              const total_estudiantes = data.data.estudiantes;
              // Extraer los valores de count de cada parámetro para actividades
              const total_quiz =
                data.data.actividades[0].obtenerparticipacionquiz;
              const total_evidencias =
                data.data.evidencias[0].obtenerparticipacionevi;
              const total_foros = data.data.foros[0].obtenerparticipacionforum;
              const total_wikis = data.data.wikis[0].obtenerparticipacionwiki;
              //Extraer los valores de count para competencias y resultados de aprendizaje
              const competencias = data.data.competencias;
              //Extraer los valores de los aprendices que cumplen con las condiciones de inasistencias
              const analisisAsis = data.data.analisisAsis;

              /* const competencias = {
                36853: [
                  495541, 495541, 495541, 495541, 495541, 495541, 495541, 495541, 495541,
                  495542, 495542, 495542, 495542, 495542, 495542, 495542, 495542, 495542, 495542, 495542,
                  495543, 495543, 495543, 495543, 495543, 495543, 495543,
                  495544, 495544, 495544, 495544, 495544,
                  495545, 495545, 495545,
                ],
                36854: [
                  495546, 495546, 495546, 495546, 495546, 495546,
                  495547, 495547, 495547,
                  495548, 495548, 495548, 495548, 495548, 495548, 495548, 495548,
                  495549, 495549, 495549, 495549, 495549, 495549, 495549, 495549, 495549, 495549, 495549,
                ],
                36855: [
                  495550, 495550, 495550, 495550, 495550, 495550,
                  495551, 495551, 495551,
                  495552, 495552, 495552, 495552, 495552, 495552, 495552, 495552,
                  495553, 495553, 495553, 495553, 495553, 495553, 495553, 495553, 495553, 495553, 495553,
                ],
              }; */

              let modalBody = document.getElementById("selectedData");
              modalBody.innerHTML = "";

              // Iterar sobre los datos
              analisisAsis.forEach(item => {
                // Extraer los valores del string 'analizar_asistencia'
                const match = item.analizar_asistencia.match(/(\d+),"([^"]+)","([^"]+)"/);

                if (match) {

                  const nombre = match[2]; // Nombre
                  const comentario = match[3]; // Comentario

                  // Crear una nueva fila
                  const row = document.createElement("tr");

                  const nombreCell = document.createElement("td");
                  nombreCell.textContent = nombre;

                  const comentarioCell = document.createElement("td");
                  comentarioCell.textContent = comentario;

                  // Agregar celdas a la fila

                  row.appendChild(nombreCell);
                  row.appendChild(comentarioCell);

                  // Agregar la fila al tbody
                  modalBody.appendChild(row);
                }
              });

              // Objeto para almacenar el conteo de valores por cada competencia
              const conteoPorCompetencia = {};

              // Recorremos cada clave del objeto `competencias`
              for (const competenciaId in competencias) {
                if (competencias.hasOwnProperty(competenciaId)) {
                  const values = competencias[competenciaId]; // Obtenemos los valores de la competencia

                  // Si no existe una entrada para esta competencia, la inicializamos como objeto vacío
                  if (!conteoPorCompetencia[competenciaId]) {
                    conteoPorCompetencia[competenciaId] = {};
                  }

                  // Iteramos sobre los valores de la competencia
                  values.forEach((valor) => {
                    // Verificamos si el valor ya tiene un conteo para esta competencia
                    if (conteoPorCompetencia[competenciaId][valor]) {
                      conteoPorCompetencia[competenciaId][valor]++;
                    } else {
                      // Si no existe, inicializamos el conteo en 1
                      conteoPorCompetencia[competenciaId][valor] = 1;
                    }
                  });
                }
              }

              const aprendices = total_estudiantes - total_suspendidos;
              const pendquiz = aprendices - total_quiz;
              const pendevi = aprendices - total_evidencias;
              const pendfor = aprendices - total_foros;
              const pendwik = aprendices - total_wikis;

              // Preparar datos
              const competenciasLabels = Object.keys(conteoPorCompetencia);
              const resultadosIds = new Set(); // Para resultados únicos

              // Obtener todos los resultados únicos
              competenciasLabels.forEach((competenciaId) => {
                const resultados = conteoPorCompetencia[competenciaId];
                for (const resultadoId in resultados) {
                  resultadosIds.add(resultadoId);
                }
              });

              const resultadosLabels = Array.from(resultadosIds); // Convertir a array de resultados únicos

              // Crear un dataset por cada resultado, no por competencia
              const datasets = resultadosLabels.map((resultadoId, index) => {
                return {
                  label: `Resultado: ${resultadoId}`,
                  data: competenciasLabels.map(
                    (competenciaId) =>
                      conteoPorCompetencia[competenciaId][resultadoId] || 0
                  ),
                  backgroundColor: `rgba(${Math.floor(
                    Math.random() * 300
                  )}, ${Math.floor(Math.random() * 300)}, ${Math.floor(
                    Math.random() * 300
                  )}, 0.5)`,
                  borderColor: `rgba(${Math.floor(
                    Math.random() * 255
                  )}, ${Math.floor(Math.random() * 255)}, ${Math.floor(
                    Math.random() * 300
                  )}, 1)`,
                  borderWidth: 1,
                };
              });

              // Modificar competenciasLabels para agregar "Competencia Nro: " a cada etiqueta
              const competenciasLabelsModificado = competenciasLabels.map((label, idx) => `Competencia Nro: ${label}`);


              // Crear el gráfico
              const ChartResultados = document
                .getElementById("ChartResultados")
                .getContext("2d");
              new Chart(ChartResultados, {
                type: "bar",
                data: {
                  labels: competenciasLabelsModificado, // Las competencias son las etiquetas en el eje X
                  datasets: datasets,
                },
                options: {
                  scales: {
                    y: {
                      beginAtZero: true,
                    },
                    x: {
                      stacked: false, // Agrupadas, cambiar a true si quieres que las barras estén apiladas
                    },
                  },
                },
              });

              // Selecciona el elemento h2 y actualiza su contenido
              document.getElementById(
                "estudiantesCount"
              ).innerText = `Total Aprendices: ${total_estudiantes}`;
              // Selecciona el elemento h2 y actualiza su contenido
              document.getElementById(
                "suspendidosCount"
              ).innerText = `Total Aprendices Suspendidos: ${total_suspendidos}`;

              // Crear la gráfica para el control de asistencias
              const ctrl_asistencia = document
                .getElementById("ChartAsistencia")
                .getContext("2d");
              new Chart(ctrl_asistencia, {
                type: "bar",
                data: {
                  labels: [
                    "Excusa Médica",
                    "Llegada Tarde",
                    "Asistencia",
                    "Inasistencia",
                    "Suspendido",
                  ],
                  datasets: [
                    {
                      label: "Control de Asistencia",
                      data: [
                        excusaMedicaCount,
                        llegadaTardeCount,
                        asistenciaCount,
                        inasistenciaCount,
                        suspendidoCount,
                      ],
                      backgroundColor: [
                        "rgba(255, 99, 132, 0.2)",
                        "rgba(54, 162, 235, 0.2)",
                        "rgba(75, 192, 192, 0.2)",
                        "rgba(153, 102, 255, 0.2)",
                        "rgba(255, 159, 64, 0.2)",
                      ],
                      borderColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                      ],
                      borderWidth: 1,
                    },
                  ],
                },
                options: {
                  scales: {
                    y: {
                      beginAtZero: true,
                    },
                  },
                },
              });
              /* ----------------------------------------------------------------------------------------------------------  */
              // Crear la gráfica con los valores obtenidos para el control de participacón de actividades
              const ChartParticipa = document
                .getElementById("ChartParticipa")
                .getContext("2d");
              new Chart(ChartParticipa, {
                type: "bar",
                data: {
                  labels: [
                    "Pruebas de Conocimiento",
                    "Evidencias",
                    "Foros",
                    "Wikis",
                  ],
                  datasets: [
                    {
                      label: "Realizadas",
                      data: [
                        total_quiz,
                        total_evidencias,
                        total_foros,
                        total_wikis,
                      ],
                      backgroundColor: [
                        "rgba(54, 162, 235, 0.2)",
                        "rgba(54, 162, 235, 0.2)",
                        "rgba(54, 162, 235, 0.2)",
                        "rgba(54, 162, 235, 0.2)",
                      ],
                      borderColor: [
                        "rgba(54, 162, 235, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(54, 162, 235, 1)",
                      ],
                      borderWidth: 1,
                    },
                    {
                      label: "Pendientes",
                      data: [pendquiz, pendevi, pendfor, pendwik],
                      backgroundColor: [
                        "rgba(255, 99, 132, 0.2)",
                        "rgba(255, 99, 132, 0.2)",
                        "rgba(255, 99, 132, 0.2)",
                        "rgba(255, 99, 132, 0.2)",
                      ],
                      borderColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(255, 99, 132, 1)",
                        "rgba(255, 99, 132, 1)",
                        "rgba(255, 99, 132, 1)",
                      ],
                      borderWidth: 1,
                    },
                  ],
                },
                options: {
                  scales: {
                    y: {
                      beginAtZero: true,
                    },
                  },
                },
              });

              /* ----------------------------------------------------------------------------------------------------------  */
              // Crear la gráfica con los valores obtenidos para las calificaciones de resultados de aprendizaje
            } else {
              console.error(
                "Error en la respuesta del servidor:",
                data.message
              );
            }

            // Aquí puedes manejar la respuesta
          })
          .catch((error) => {
            console.error("Error en la solicitud:", error);
          });
      } else {
        alert("Por favor, complete todos los campos.");
      }
    });
});
