$(document).ready(function() {
    $('#table_asiss').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 10,
        ordering: false, // Desactivamos el ordenamiento para mantener los grupos
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json",
          },
          scrollX: true,
        drawCallback: function(settings) {
            // Restaurar la estructura visual después de cada redibujado
            var api = this.api();
            var rows = api.rows().nodes();
            var last = null;

            // Agrupar visualmente las filas por estudiante
            api.rows().every(function(rowIdx) {
                var tr = $(rows[rowIdx]);
                var studentId = tr.data('student-id');
                
                if (last !== studentId) {
                    // Primera fila del grupo
                    tr.find('.student-cell').show();
                    tr.find('.hidden-cell').hide();
                    last = studentId;
                } else {
                    // Filas subsiguientes del grupo
                    tr.find('.student-cell').hide();
                    tr.find('.hidden-cell').show();
                }
            });
        },
    });
});

document.getElementById('back-button').addEventListener('click', function() {
    window.history.back(); // Regresa a la página anterior
});
