document.addEventListener('DOMContentLoaded', function () {
    const gestor = document.getElementById('gestorImagenDestacada');
    if (!gestor) return;

    // Elementos del gestor
    const previewContenedor = document.getElementById('previewImagenDestacada');
    const inputId = document.getElementById('imagenDestacadaId');
    const seleccionarBtn = document.getElementById('seleccionarImagenBtn');
    const quitarBtn = document.getElementById('quitarImagenBtn');

    // Elementos del Modal
    const modal = document.getElementById('modalSeleccionMedios');
    const cerrarModalBtn = modal.querySelector('.modal-cerrar');
    const galeriaContenedor = document.getElementById('galeriaModalContenedor');
    const cancelarBtn = document.getElementById('cancelarSeleccionBtn');

    // --- Funciones ---

    function abrirModal() {
        modal.style.display = 'flex';
        cargarGaleria();
    }

    function cerrarModal() {
        modal.style.display = 'none';
        galeriaContenedor.innerHTML = '<p>Cargando medios...</p>'; // Resetear
    }

    async function cargarGaleria() {
        try {
            const respuesta = await fetch('/panel/ajax/obtener-galeria');
            if (!respuesta.ok) throw new Error('Error de red al cargar la galería.');

            const datos = await respuesta.json();
            if (!datos.exito) throw new Error(datos.mensaje || 'No se pudo cargar la galería.');

            renderizarGaleria(datos.media);
        } catch (error) {
            galeriaContenedor.innerHTML = `<p style="color: red;">${error.message}</p>`;
            console.error(error);
        }
    }

    function renderizarGaleria(mediaItems) {
        galeriaContenedor.innerHTML = ''; // Limpiar
        if (mediaItems.length === 0) {
            galeriaContenedor.innerHTML = '<p>No hay imágenes en la biblioteca.</p>';
            return;
        }

        mediaItems.forEach(item => {
            const div = document.createElement('div');
            div.className = 'modal-media-item';
            div.dataset.id = item.id;
            // CORRECCIÓN: Usar 'url_publica' que es la URL completa generada por el accesor.
            div.dataset.url = item.url_publica;
            div.innerHTML = `<img src="${item.url_publica}" alt="${item.titulo}" loading="lazy">`;

            div.addEventListener('click', () => {
                // CORRECCIÓN: Pasar la URL pública completa a la función de selección.
                seleccionarImagen(item.id, item.url_publica);
                cerrarModal();
            });

            galeriaContenedor.appendChild(div);
        });
    }

    function seleccionarImagen(id, url) {
        inputId.value = id;
        previewContenedor.innerHTML = `<img src="${url}" alt="Imagen destacada">`;
        quitarBtn.style.display = 'inline-flex';
        seleccionarBtn.textContent = 'Cambiar imagen';
    }

    function quitarImagen() {
        inputId.value = '';
        previewContenedor.innerHTML = '';
        quitarBtn.style.display = 'none';
        seleccionarBtn.textContent = 'Seleccionar imagen';
    }

    // --- Event Listeners ---
    seleccionarBtn.addEventListener('click', abrirModal);
    quitarBtn.addEventListener('click', quitarImagen);
    cerrarModalBtn.addEventListener('click', cerrarModal);
    cancelarBtn.addEventListener('click', cerrarModal);

    window.addEventListener('click', event => {
        if (event.target === modal) {
            cerrarModal();
        }
    });
});