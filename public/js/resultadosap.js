// Función para codificar en Base64
function encodeBase64(str) {
  return btoa(str);
}

//función para display fullname de resultado de aprendizaje
function showFullName(element, fullName, shortName) {
  // Encuentra el <th> contenedor
  const th = element.parentElement;
  // Encuentra el <p> con el nombre corto
  const p = th.querySelector(".rea-name");
  // Mostrar el nombre completo
  p.textContent = fullName;
  // Ocultar el botón "Leer más" y mostrar "Leer menos"
  element.style.display = "none";
  th.querySelector(".readless-btn").style.display = "inline";
  // Agregar la clase que ajusta el tamaño
  th.classList.add("expanded");
}
//funcion ocultar fullname resultado aprendizaje
function showShortName(element, shortName, fullName) {
  // Encuentra el <th> contenedor
  const th = element.parentElement;
  // Encuentra el <p> con el nombre corto
  const p = th.querySelector(".rea-name");
  // Mostrar el nombre corto con puntos suspensivos
  p.textContent = shortName;
  // Ocultar el botón "Leer menos" y mostrar "Leer más"
  element.style.display = "none";
  th.querySelector(".readmore-btn").style.display = "inline";
  // Remover la clase que ajusta el tamaño
  th.classList.remove("expanded");
}

// Función para decodificar en Base64
function decodeBase64(str) {
  return atob(str);
}
/* Funciòn de redirecciòn la pagina de resultadoap -> compotencias */
function redirectToCompetencias(encoded_curso) {
  window.location.href = `../competencias.php?idnumber=${encoded_curso}`;
}

function cleanText(text) {
  // Eliminar caracteres no permitidos, manteniendo letras, dígitos, espacios y signos de puntuación en español
  const cleanedText = text.replace(
    /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s.,;:¿?¡!()'"-]/g,
    ""
  );
  return cleanedText;
}

function toSentenceCase(text) {
  const cleanedText = cleanText(text);
  return (
    cleanedText.charAt(0).toUpperCase() + cleanedText.slice(1).toLowerCase()
  );
}

/* Funciòn de redirecciòn la pagina de Competencias -> resultadoap */
function redirectComToResultados(encoded_ficha, encoded_competencia) {
  const urlParams = `id_ficha=${encoded_ficha}&id_competencia=${encoded_competencia}`;
  const encodedParams = encodeBase64(urlParams);
  window.location.href = `../views/resultados/resultadoap.php?params=${encodedParams}`;
}

/* Funciòn de redirecciòn la pagina de resultadoap -> resultado */
function redirectToResultado(encoded_curso, encoded_competencia, encode_rea) {
  const urlParams = `curso=${encoded_curso}&id_competencia=${encoded_competencia}&rea_id=${encode_rea}`;
  const encodedParams = encodeBase64(urlParams);
  window.location.href = `../../views/resultados/resultados.php?params=${encodedParams}`;
}
// Obtener numero de ficha
var ficha = document.getElementById("titulo_ficha").innerHTML;
// Obtener nombre de la ficha
var title_ficha = document
  .getElementById("titulo_nombre")
  .getAttribute("title");
// Obtener nombre del usuario
var username = document.getElementById("navbarDropdown").innerHTML;
// Obtener el codigo de la competencia
var elemento = document.getElementById("id_comp");
var cod_compentencia = elemento.textContent || elemento.innerText;
// Obtener el nombre de la competencia
var name_comp = document.getElementById("name_comp");
var name_compentencia = name_comp.textContent || name_comp.innerText;

var d = new Date();
var date =
  d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
var time = d.toLocaleString("es-CO", {
  hour: "numeric",
  minute: "numeric",
  second: "numeric",
  hour12: true,
});
var dateTime = date + " " + time;


$(document).ready(function () {
  var table = new DataTable("#tabla", {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json",
    },
    colReorder: true,
    scrollX: true,
    dom: '<"row"<"col-md-4"B><"col-md-4"f><"col-md-4"l>rtip',
    buttons: [
      //  Boton para exportar archivos en formato Excel
      {
        extend: "excelHtml5",
        text: '<i class="fas fa-file-excel"></i>',
        titleAttr: "Exportar Excel",
        title: "Resultados de Aprendizaje",
        className: 'btn-excel',
        filename: function () {
          var d = new Date();
          var date =
            d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
          return (
            "Resultados de Aprendizaje - Competencia No." +
            cod_compentencia +
            "-" +
            date
          );
        },
        exportOptions: {
          columns: ":visible",
          format: {
            header: function (data, columnIndex) {
              // Limpiar etiquetas HTML del encabezado y limitar a 25 caracteres
              var maxLength = 25;
              var cleanData = data.replace(/<\/?[^>]+(>|$)/g, ""); // Remueve etiquetas HTML
              // Asegurarse de que el encabezado no muestre "Leer más" o "Leer menos"
              cleanData = cleanData.replace(/Leer más|Leer menos/g, '').trim(); // Elimina estos textos si están presentes
              // Limitar el texto si es mayor a maxLength y agregar puntos suspensivos
              return cleanData.length > maxLength ? cleanData.substr(0, maxLength) + '...' : cleanData;
            },

            body: function (data, row, column, node) {
              // Verificar si el nodo contiene un elemento <select> y obtener el valor seleccionado
              var selectElement = $(node).find("select");
              if (selectElement.length > 0) {
                return $(node).find(".selected-resultado").val(); // Obtener el valor seleccionado del <select>
              }

              // Verificar si el nodo contiene un elemento checkbox
              var checkboxElement = $(node).find('input[type="checkbox"]');
              if (checkboxElement.length > 0) {
                return ""; // Retorna vacío para checkboxes
              }

              // Remover etiquetas HTML del contenido del cuerpo
              var cleanData = data.replace(/<\/?[^>]+(>|$)/g, "");

              // Asegurarse de que el cuerpo no muestre "Leer más" o "Leer menos"
              return cleanData.replace(/Leer más|Leer menos/g, '').trim(); // Elimina estos textos si están presentes
            },

          },
        },
        customize: function (xlsx) {
          var sheet = xlsx.xl.worksheets["sheet1.xml"];
          var formattedDate = "Documento Generado: " + dateTime;
          var additionalData =
            "No Ficha" +
            ficha +
            "-" +
            title_ficha +
            "\nCompetencia No." +
            cod_compentencia +
            ": " +
            name_compentencia;

          var additionalIndice = "\n" + "INDICE DE RESULTADOS DE APRENDIZAJE:";
          // Generar el índice de resultados de aprendizaje usando reaData
          var indiceText = reaData
            .map(function (item, index) {
              var idText = toSentenceCase(index + 1 + ": " + item.id);
              var nombreText = toSentenceCase(item.nombre);
              return idText + " - " + nombreText;
            })
            .join("\n");

          // Crear una nueva fila con la fecha y el datos adicional en una sola fila
          var newRow =
            '<row r="1"><c t="inlineStr" r="A1"><is><t>' + username + "\n" + formattedDate + "\n" + additionalData + "\n" + additionalIndice + "\n" + indiceText + "</t></is></c></row>";
          // Ajustar los índices de las filas existentes
          $("row", sheet).each(function () {
            var r = parseInt($(this).attr("r"));
            $(this).attr("r", r + 1);
            $("c", this).each(function () {
              var ref = $(this).attr("r");
              var col = ref.substring(0, 1);
              var row = parseInt(ref.substring(1)) + 1;
              $(this).attr("r", col + row);
            });
          });

          // Insertar la nueva fila al principio del archivo Excel
          sheet.childNodes[0].childNodes[1].innerHTML =
            newRow + sheet.childNodes[0].childNodes[1].innerHTML;

          // Añadir estilo (negrilla) a las celdas (s="1" referencia al estilo en la hoja de estilos)
          var styleSheet = xlsx.xl["styles.xml"];
          var cellXfs = $("cellXfs", styleSheet);
          cellXfs.append('<xf xfId="0" applyFont="1" fontId="1"/>');
          var fonts = $("fonts", styleSheet);
          fonts.append(
            '<font><b/><sz val="11"/><color rgb="000000"/><name val="Calibri"/></font>'
          );
        },
      },
      //  Boton para exportar archivos en formato Csv
      {
        extend: "csvHtml5",
        text: '<i class="fas fa-file-csv"></i>',
        titleAttr: "Exportar Csv",
        className: 'btn-csv',
        title: "Resultados de Aprendizaje",
        filename: function () {
          var d = new Date();
          var date =
            d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
          return (
            "Resultados de Aprendizaje - Competencia No." +
            cod_compentencia +
            "-" +
            date
          );
        },
        exportOptions: {
          columns: ":visible",
          format: {
            header: function (data, columnIndex) {
              // Limpiar etiquetas HTML del encabezado y limitar a 25 caracteres
              var maxLength = 25;
              var cleanData = data.replace(/<\/?[^>]+(>|$)/g, ""); // Remueve etiquetas HTML

              // Asegurarse de que el encabezado no muestre "Leer más" o "Leer menos"
              cleanData = cleanData.replace(/Leer más|Leer menos/g, '').trim(); // Elimina estos textos si están presentes

              // Limitar el texto si es mayor a maxLength y agregar puntos suspensivos
              return cleanData.length > maxLength ? cleanData.substr(0, maxLength) + '...' : cleanData;
            },

            body: function (data, row, column, node) {
              // Verificar si el nodo contiene un elemento <select> y obtener el valor seleccionado
              var selectElement = $(node).find("select");
              if (selectElement.length > 0) {
                return $(node).find(".selected-resultado").val(); // Obtener el valor seleccionado del <select>
              }

              // Verificar si el nodo contiene un elemento checkbox
              var checkboxElement = $(node).find('input[type="checkbox"]');
              if (checkboxElement.length > 0) {
                return ""; // Retorna vacío para checkboxes
              }

              // Remover etiquetas HTML del contenido del cuerpo
              var cleanData = data.replace(/<\/?[^>]+(>|$)/g, "");

              // Asegurarse de que el cuerpo no muestre "Leer más" o "Leer menos"
              return cleanData.replace(/Leer más|Leer menos/g, '').trim(); // Elimina estos textos si están presentes
            },

          },
        },
        customize: function (csv) {
          var formattedDate = "Documento Generado: " + dateTime;
          var additionalData =
            "No Ficha" +
            ficha +
            "-" +
            title_ficha +
            "\nCompetencia No." +
            cod_compentencia +
            ": " +
            name_compentencia;
          var additionalIndice = "\n" + "INDICE DE RESULTADOS DE APRENDIZAJE:";
          // Generar el índice de resultados de aprendizaje usando reaData
          var indiceText = reaData
            .map(function (item, index) {
              var idText = toSentenceCase(index + 1 + ": " + item.id);
              var nombreText = toSentenceCase(item.nombre);
              return idText + " - " + nombreText;
            })
            .join("\n");

          // Agregar la fecha y el dato adicional como filas en el contenido CSV
          var newCsv = username + "\n" + formattedDate + "\n" + additionalData + "\n" + additionalIndice + "\n" + indiceText + "\n" + csv;
          return newCsv;
        },
      },
      //  Boton para exportar archivos en formato Pdf
      {
        extend: "pdfHtml5",
        text: '<i class="fas fa-file-pdf"></i>',
        titleAttr: "Exportar Pdf",
        className: 'btn-pdf',
        title: "Resultados de Aprendizaje",

        filename: function () {
          var d = new Date();
          var date =
            d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
          return (
            "Resultados de Aprendizaje - Competencia No." +
            cod_compentencia +
            "-" +
            date
          );
        },
        // ajuste de pag pdf
        orientation: "landscape", // Orientación horizontal
        pageSize: "A2", // Tamaño de la página
        autoWidth: false, // Ajustar automáticamente el ancho de las columnas
        exportOptions: {
          columns: ":visible",
          format: {
            header: function (data, columnIndex) {
              // Limpiar etiquetas HTML del encabezado y limitar a 25 caracteres
              var maxLength = 25;
              var cleanData = data.replace(/<\/?[^>]+(>|$)/g, ""); // Remueve etiquetas HTML

              // Asegurarse de que el encabezado no muestre "Leer más" o "Leer menos"
              cleanData = cleanData.replace(/Leer más|Leer menos/g, '').trim(); // Elimina estos textos si están presentes

              // Limitar el texto si es mayor a maxLength y agregar puntos suspensivos
              return cleanData.length > maxLength ? cleanData.substr(0, maxLength) + '...' : cleanData;
            },

            body: function (data, row, column, node) {
              // Verificar si el nodo contiene un elemento <select> y obtener el valor seleccionado
              var selectElement = $(node).find("select");
              if (selectElement.length > 0) {
                return $(node).find(".selected-resultado").val(); // Obtener el valor seleccionado del <select>
              }

              // Verificar si el nodo contiene un elemento checkbox
              var checkboxElement = $(node).find('input[type="checkbox"]');
              if (checkboxElement.length > 0) {
                return ""; // Retorna vacío para checkboxes
              }

              // Remover etiquetas HTML del contenido del cuerpo
              var cleanData = data.replace(/<\/?[^>]+(>|$)/g, "");

              // Asegurarse de que el cuerpo no muestre "Leer más" o "Leer menos"
              return cleanData.replace(/Leer más|Leer menos/g, '').trim(); // Elimina estos textos si están presentes
            },

          },
        },
        customize: function (doc) {
          var d = new Date();
          var date =
            d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
          var time = d.toLocaleString("es-CO", {
            hour: "numeric",
            minute: "numeric",
            second: "numeric",
            hour12: true,
          });

          var dateTime = date + " " + time;
          var formattedDate = username + "\n Documento Generado: " + dateTime;
          var additionalData =
            "No Ficha" +
            ficha +
            "-" +
            title_ficha +
            "\nCompetencia No." +
            cod_compentencia +
            ": " +
            name_compentencia;

          var additionalIndice = "\n" + "INDICE DE RESULTADOS DE APRENDIZAJE:";
          // Generar el índice de resultados de aprendizaje usando reaData
          var indiceText = reaData
            .map(function (item, index) {
              var idText = toSentenceCase(index + 1 + ": " + item.id);
              var nombreText = toSentenceCase(item.nombre);
              return idText + " - " + nombreText;
            })
            .join("\n");

          // Agregar la fecha, el dato adicional y el índice como una fila en el contenido del PDF
          doc.content.splice(1, 0, {
            text:
              formattedDate +
              "\n" +
              additionalData +
              "\n" +
              additionalIndice +
              "\n" +
              indiceText,
            margin: [0, 0, 0, 12],
          });
        },
      },
      //  Boton para restaurar las columnas ocultas
      {
        extend: "excelHtml5",
        className: " restart",
        text: '<i class="fas fa-eye"></i> &nbsp;Restaurar Columnas',
        action: function (e, dt, node, config) {
          restoreAllColumns(dt);
        },
      },
    ],
    ordering: false,
    paging: true,
    pageLength: 10, // # de datos por pagina
    lengthMenu: [
      [10, 15, 25, 50, 80, -1],
      ["10", "15", "25", "50", "80", "Mostrar todo"],
    ],
    initComplete: function () {
      var api = this.api();
      var columnVisibility = {};

      // Función para actualizar los botones de visibilidad
      function updateVisibilityButtons() {
        api.columns().every(function () {
          var column = this;
          var columnIndex = column.index();

          if (!columnVisibility.hasOwnProperty(columnIndex)) {
            columnVisibility[columnIndex] = column.visible();
          }

          var header = $(column.header());
          var span = header.find(".visibility-toggle");

          if (span.length === 0) {
            span = $(
              '<span class="btn ml-2 visibility-toggle" id="ojito"></span>'
            );
            span.on("click", function () {
              var visible = !column.visible();
              column.visible(visible);
              // Cambiar estilo basado en visibilidad
              if (visible) {
                span.removeClass("column-hidden").addClass("column-visible");
              } else {
                span.removeClass("column-visible").addClass("column-hidden");
              }
            });

            // Aplicar clase inicial basada en la visibilidad actual de la columna
            if (column.visible()) {
              span.addClass("column-visible");
            } else {
              span.addClass("column-hidden");
            }

            header.append(span);
          }
        });
      }

      // Inicializar los botones de visibilidad
      updateVisibilityButtons();

      // Actualizar los botones de visibilidad después de la reordenación
      api.on("column-reorder", function () {
        // Limpiar y reconstruir los botones de visibilidad
        $(".visibility-toggle").remove();
        updateVisibilityButtons();

        // Ajustar y redibujar la tabla después de la reordenación
        table.columns.adjust().draw();
      });

      // Botón para restaurar todas las columnas
      $("#restore-columns").on("click", function () {
        restoreAllColumns(api);
      });

      $("#container").css("display", "block");
      table.columns.adjust().draw();
    },
  });

  function restoreAllColumns(tableApi) {
    tableApi.columns().visible(true);

    // Re-inicializar los botones de visibilidad
    updateVisibilityButtons(tableApi);
  }

  function updateVisibilityButtons(tableApi) {
    // Limpiar y reconstruir los botones de visibilidad
    $(".visibility-toggle").remove(); // Remueve botones antiguos

    tableApi.columns().every(function () {
      var column = this;
      var columnIndex = column.index();

      var header = $(column.header());
      var span = $(
        '<span class="btn ml-2 visibility-toggle lang_sign_resultadoap"  id="ojito"></span>'
      ).on("click", function () {
        var visible = !column.visible();
        column.visible(visible);
        // Cambiar estilo basado en visibilidad
        if (visible) {
          span.removeClass("column-hidden").addClass("column-visible");
        } else {
          span.removeClass("column-visible").addClass("column-hidden");
        }
      });

      // Aplicar clase inicial basada en la visibilidad actual de la columna
      if (column.visible()) {
        span.addClass("column-visible");
      } else {
        span.addClass("column-hidden");
      }

      header.append(span);
    });
  }
});

