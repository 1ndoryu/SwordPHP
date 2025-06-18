/**
 * Crea y envía dinámicamente un formulario para eliminar un recurso.
 * Esto permite tener botones de eliminar dentro de otros formularios sin anidarlos,
 * manteniendo la protección CSRF.
 *
 * @param {string} url La URL del endpoint para la eliminación.
 * @param {string} csrfToken El token CSRF para la validación.
 * @param {string} [mensaje] El mensaje de confirmación opcional.
 */
function eliminarRecurso(url, csrfToken, mensaje) {
    const confirmacion = confirm(mensaje || '¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.');

    if (confirmacion) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}
