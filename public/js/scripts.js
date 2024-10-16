/*!
* Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
*/

// Scripts redirección a zajuna
function redirectToZajuna() {
    window.location.href = `/zajuna/my/`;
}
// Script para manejar el evento "Leer más" y "Leer menos" en las tarjetas de categorias
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.read-more-comp').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            var textCompetencia = this.previousElementSibling;
            var fullText = textCompetencia.getAttribute('data-full-text');
            var limitedText = textCompetencia.getAttribute('data-limited-text');

            // Alternar entre mostrar el texto completo y truncado
            if (this.textContent === "Leer más") {
                textCompetencia.textContent = fullText; // Mostrar texto completo
                this.textContent = "Leer menos";
            } else {
                textCompetencia.textContent = limitedText; // Mostrar texto truncado
                this.textContent = "Leer más";
            }
        });
    });
});
// Ajuste para detalles en los titles
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').hover(
        function () {
            var element = $(this);
            // Mostrar el tooltip
            element.tooltip('show');

            // Iniciar temporizador para ocultarlo después de 1 segundos
            element.data('tooltipTimeout', setTimeout(function () {
                element.tooltip('hide');
            }, 3000));
        },
        function () {
            // Cancelar el temporizador si el cursor sale del elemento
            clearTimeout($(this).data('tooltipTimeout'));
            $(this).tooltip('hide'); //ocultar el tooltip inmediatamente al salir
        }
    );
});


window.addEventListener('DOMContentLoaded', event => {
    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
        // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        //     document.body.classList.toggle('sb-sidenav-toggled');
        // }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }
    /*cambiará el color del texto a verde cuando el cursor esté sobre los enladdEventListeneraces y 
    volverá al color predeterminado cuando se retire el cursor. 
    ubicacion en header de navbar-brand ps-4.*/
    const links = document.querySelectorAll('.navbar-brand a');
    links.forEach(link => {
        link.addEventListener('mouseenter', () => {
            link.style.color = '#39a900';
        });
        link.addEventListener('mouseleave', () => {
            link.style.color = ''; // Revertir al color predeterminado
        });
    });
    /*cambiará el color del texto a verde cuando el cursor esté sobre los enlaces y 
    volverá al color predeterminado cuando se retire el cursor. 
    ubicacion en id="zajuna-link" class="navbar-brand ps-5"*/
    const zajunaLink = document.getElementById('zajuna-link');

    zajunaLink.addEventListener('mouseenter', () => {
        zajunaLink.style.color = '#39a900';
    });
    zajunaLink.addEventListener('mouseleave', () => {
        zajunaLink.style.color = ''; // Revertir al color predeterminado
    });
});

// FUNCION QUE EVALUA SI EL NAVEGADOR ES FIREFOX NO IMPRIME MENSAJE DE RECOMENDACION
$(document).ready(function () {
    var isFirefox = typeof InstallTrigger !== 'undefined';
    if (!isFirefox) {
        $('#browser-alert').show();
    }
});




