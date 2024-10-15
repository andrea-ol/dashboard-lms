document.addEventListener('DOMContentLoaded', function () {
  const accessibilityButtonComp = document.getElementById('accessibility-button');
  const accessibilityOptionsComp = document.getElementById('accessibility-options');
  const dauditiva = document.getElementById('boton_auditivo');
  const accessibilityText = dauditiva ? dauditiva.querySelector('.accessibility-text') : null;

  // Inicializar el popover para el botón de accesibilidad
  if (dauditiva) {
    new bootstrap.Popover(dauditiva, {
      html: true,
      trigger: "hover",
      placement: "top",
      content: '<img src="https://media.giphy.com/media/3o7aD6WXl0cN15LQmY/giphy.gif" width="200" alt="GIF">',
      customClass: "popover-gif",
    });
  }
  if (accessibilityButtonComp && accessibilityOptionsComp) {
    accessibilityButtonComp.addEventListener('click', function () {
      $(accessibilityOptionsComp).toggle();
    });

    $(document).on('click', function (event) {
      if (!accessibilityButtonComp.contains(event.target) && !accessibilityOptionsComp.contains(event.target)) {
        $(accessibilityOptionsComp).hide();
      }
    });
  }

  function applyAccessibility() {
    function createPopovers(selector, imgSrc) {
      $(selector).popover({
        html: true,
        trigger: 'hover',
        placement: 'top',
        content: `<img src="${imgSrc}" width="200" alt="GIF">`,
        template: '<div class="popover popover-gif" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
      });
    }
    // Se crea el Popovers, a cada  clase se asigna el gif que se mostrara en la vista.
    createPopovers('.lang_sign', '../public/assets/img/lenguaje-senas.gif');
    createPopovers('.lang_sign_resultadoap', '../../public/assets/img/lenguaje-senas.gif');
    createPopovers('.thead_resultadoap', '../../public/assets/img/lenguaje.gif');
    createPopovers('.lang_sign_resultados', '../../public/assets/img/lenguaje-senas.gif');
    createPopovers('.thead_resultados', '../../public/assets/img/lenguaje.gif');
    createPopovers('.item-nav, .option_nav', '/lms-califica/public/assets/img/header-acces.webp');
    createPopovers('.btn-excel', '/lms-califica/public/assets/img/excel.gif');
    createPopovers('.btn-csv', '/lms-califica/public/assets/img/csv.gif');
    createPopovers('.btn-pdf', '/lms-califica/public/assets/img/pdf.gif');
    createPopovers('.restart', '/lms-califica/public/assets/img/restart.gif');


  }
  // Luego de desactivar la accesibilidad, la funcion remueve los Popover
  function removeAccessibility() {
    $('.lang_sign, .lang_sign_resultadoap, .thead_resultadoap, .lang_sign_resultados, .thead_resultados, .item-nav, .option_nav, .buttons-excel, .btn-csv, .btn-pdf, .restart').each(function () {
      if ($(this).data('bs.popover')) {
        $(this).popover('dispose');
      }
    });

    // Verificar si los popovers fueron removidos correctamente
    const popoversRemoved = $('.lang_sign, .lang_sign_resultadoap, .thead_resultadoap, .lang_sign_resultados, .thead_resultados, .item-nav, .option_nav, .buttons-excel, .btn-csv, .btn-pdf, .restart').filter(function () {
      return $(this).data('bs.popover');
    }).length === 0;

    if (!popoversRemoved) {
      console.error('Algunos popovers no fueron removidos correctamente.');
    }

    // Aquí puedes agregar más código para revertir otros cambios de accesibilidad si los hay
  }

  function updateAccessibilityState() {
    const isEnabled = localStorage.getItem('accessibilityEnabled') === 'true';
    if (isEnabled) {
      applyAccessibility();
      if (accessibilityText) accessibilityText.textContent = 'Desactivar';
    } else {
      removeAccessibility();
      if (accessibilityText) accessibilityText.textContent = 'Activar';
    }
  }


  // Verificar y aplicar el estado de accesibilidad al cargar la página
  updateAccessibilityState();

  if (dauditiva) {
    dauditiva.addEventListener('click', function (event) {
      event.preventDefault();

      const isCurrentlyEnabled = localStorage.getItem('accessibilityEnabled') === 'true';
      const newState = !isCurrentlyEnabled;

      localStorage.setItem('accessibilityEnabled', newState.toString());
      updateAccessibilityState();

      Swal.fire({
        title: newState ? "Accesibilidad Activada!" : "Accesibilidad Desactivada",
        confirmButtonText: "Continuar",
        confirmButtonColor: "#28a745",
        icon: "success",
        iconColor: "#39a900"
      });

      // Confirmar que la acción de desactivación deshabilitó correctamente la accesibilidad
      if (!newState) {
        const isDisabled = $('.lang_sign').data('bs.popover') === undefined;
        // Recarga la pagina luego de desactivar la accesibilidad
        location.reload();
        if (!isDisabled) {
          console.error('La accesibilidad no se desactivó correctamente.');
        }
      }
    });
  }
});
