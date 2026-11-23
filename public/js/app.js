/* KusiMikuy: menú y validaciones de ayuda. PHP realiza la autenticación y el guardado. */

// 1. Abrir y cerrar el menú en teléfonos.
const botonMenu = document.getElementById("boton-menu");
const menu = document.getElementById("menu");

if (botonMenu && menu) {
  botonMenu.addEventListener("click", function () {
    const abierto = menu.classList.toggle("abierto");
    botonMenu.setAttribute("aria-expanded", abierto);
  });
}

// 3. Mostrar un error de validación junto al formulario.
function mostrarError(formulario, campo, texto) {
  const mensaje = formulario.querySelector(".mensaje");
  mensaje.textContent = texto;
  mensaje.classList.add("error");
  campo.focus();
}

// 4. Validar los formularios que existen en la página actual.
const formularios = document.querySelectorAll("form[data-tipo]");

formularios.forEach(function (formulario) {
  formulario.addEventListener("submit", function (evento) {
    // El envío continúa hacia PHP cuando los datos son válidos.
    const campos = formulario.elements;
    const tipo = formulario.dataset.tipo;
    const mensaje = formulario.querySelector(".mensaje");
    mensaje.textContent = "";
    mensaje.classList.remove("error");

    // required, type, min y minlength se validan desde el propio HTML.
    if (!formulario.reportValidity()) { evento.preventDefault(); return; }

    // Evitar que los campos obligatorios contengan solo espacios.
    for (const campo of campos) {
      if (campo.required && ["text", "textarea"].includes(campo.type)) {
        if (!campo.value.trim()) {
          evento.preventDefault();
          mostrarError(formulario, campo, "Completa el campo; no debe contener solo espacios.");
          return;
        }
      }
    }

    if (tipo === "registro" && campos.clave.value !== campos["confirmar-clave"].value) {
      evento.preventDefault();
      mostrarError(formulario, campos["confirmar-clave"], "Las contraseñas deben coincidir.");
      return;
    }

    if (tipo === "donacion") {
      if (campos.unidad.value !== "kg" && !Number.isInteger(Number(campos.cantidad.value))) {
        evento.preventDefault();
        mostrarError(formulario, campos.cantidad, "Las unidades y raciones deben ser números enteros.");
        return;
      }
      if (new Date(campos.limite.value).getTime() <= Date.now()) {
        evento.preventDefault();
        mostrarError(formulario, campos.limite, "El límite debe ser una fecha y hora futuras.");
        return;
      }
      if (campos.categoria.value === "Comida preparada" && campos.preparacion.value.trim().length < 5) {
        evento.preventDefault();
        mostrarError(formulario, campos.preparacion, "Describe los ingredientes y la fecha de elaboración para la revisión del administrador.");
        return;
      }
      const foto = campos.foto.files[0];
      if (foto && (!["image/jpeg", "image/png", "image/webp"].includes(foto.type) || foto.size > 5 * 1024 * 1024)) {
        evento.preventDefault();
        mostrarError(formulario, campos.foto, "La foto debe ser JPG, PNG o WebP y no superar 5 MB.");
        return;
      }
    }

    if (tipo === "traslado" && new Date(campos.fecha.value).getTime() <= Date.now()) {
      evento.preventDefault();
      mostrarError(formulario, campos.fecha, "Elige una fecha y hora futuras para el traslado.");
      return;
    }

    if (tipo === "recepcion" && campos.calidad.value === "observaciones" && !campos.observaciones.value.trim()) {
      evento.preventDefault();
      mostrarError(formulario, campos.observaciones, "Describe las observaciones del alimento recibido.");
      return;
    }

    // PHP valida otra vez, guarda y redirige. Nunca simular el resultado aquí.
  });
});
