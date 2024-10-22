// Ajuste datatable vistas aprendiz
$(document).ready(function () {
    var table = new DataTable("#table_asiss", {
      language: {
        url: "https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json",
      },
      colReorder: true,
      scrollX: true,
      ordering: false,
      paging: true,
      pageLength: 10, // # de datos por pagina
      lengthMenu: [[10, 15, 25, 50, -1], ['10', '15', '25', '50', 'Mostrar todo']],
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
              span = $('<span class="btn ml-2 visibility-toggle" id="ojito"></span>');
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
        api.on('column-reorder', function () {
          // Limpiar y reconstruir los botones de visibilidad
          $(".visibility-toggle").remove();
          updateVisibilityButtons();
  
          // Ajustar y redibujar la tabla después de la reordenación
          table.columns.adjust().draw();
        });
  
        // Botón para restaurar todas las columnas
        $('#restore-columns').on('click', function () {
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
        var span = $('<span class="btn ml-2 visibility-toggle" id="ojito"></span>')
          .on("click", function () {
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