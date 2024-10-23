// Ajuste DataTable vistas aprendiz
$(document).ready(function () {
    var table = new DataTable("#table_asiss", {
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json",
        },
        colReorder: true,
        scrollX: true,
        paging: true,
        pageLength: 10, // # de datos por página
        lengthMenu: [[10, 15, 25, 50, -1], ['10', '15', '25', '50', 'Mostrar todo']],
        initComplete: function () {
            var api = this.api();
            var columnVisibility = {};

            // Función para actualizar los botones de visibilidad
            function updateVisibilityButtons() {
                api.columns().every(function () {
                    var column = this;
                    var columnIndex = column.index();

                    // Verifica si ya se ha inicializado el botón de visibilidad para esta columna
                    if (!columnVisibility.hasOwnProperty(columnIndex)) {
                        columnVisibility[columnIndex] = column.visible();
                    }

                    var header = $(column.header());
                    var span = header.find(".visibility-toggle");

                    // Si no existe el botón, lo crea
                    if (span.length === 0) {
                        span = $('<span class="btn ml-2 visibility-toggle" id="ojito"></span>');
                        span.on("click", function () {
                            var visible = !column.visible();
                            column.visible(visible);
                            updateButtonClass(span, visible);
                        });

                        // Aplica clase inicial basada en la visibilidad actual de la columna
                        updateButtonClass(span, column.visible());
                        header.append(span);
                    }
                });
            }

            // Función para actualizar la clase de visibilidad del botón
            function updateButtonClass(button, isVisible) {
                if (isVisible) {
                    button.removeClass("column-hidden").addClass("column-visible");
                } else {
                    button.removeClass("column-visible").addClass("column-hidden");
                }
            }

            // Inicializar los botones de visibilidad
            updateVisibilityButtons();

            // Actualizar los botones de visibilidad después de la reordenación de columnas
            api.on('column-reorder', function () {
                $(".visibility-toggle").remove();
                updateVisibilityButtons();
                table.columns.adjust().draw();
            });

            // Botón para restaurar la visibilidad de todas las columnas
            $('#restore-columns').on('click', function () {
                restoreAllColumns(api);
            });

            $("#container").css("display", "block");
            table.columns.adjust().draw();
        },
    });

    // Función para restaurar todas las columnas a visibles
    function restoreAllColumns(tableApi) {
        tableApi.columns().visible(true);
        updateVisibilityButtons(tableApi);
    }
});
