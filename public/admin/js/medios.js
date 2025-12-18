/**
 * Modulo de Gestion de Medios
 * Maneja la libreria de medios: upload, seleccion, detalles y eliminacion.
 */

let medioSeleccionadoId = null;

/*
 * Inicializacion del modulo
 */
function inicializarMedios() {
    configurarEventosUpload();
    configurarDragDrop();
    configurarFormularioDetalles();
    configurarModalEliminar();
    configurarVistaToggle();
    configurarTecladoYModal();
}

/*
 * Configurar eventos de upload de archivos
 */
function configurarEventosUpload() {
    const botonSubir = document.getElementById('botonSubirArchivo');
    const inputOculto = document.getElementById('inputArchivoOculto');

    botonSubir?.addEventListener('click', function () {
        inputOculto.click();
    });

    inputOculto?.addEventListener('change', function (e) {
        if (e.target.files.length > 0) {
            subirArchivos(e.target.files);
        }
    });
}

/*
 * Configurar drag and drop
 */
function configurarDragDrop() {
    const zonaDrop = document.getElementById('zonaDropMedios');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evento => {
        document.body.addEventListener(evento, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(evento => {
        document.body.addEventListener(
            evento,
            () => {
                zonaDrop.classList.add('zonaDropActiva');
            },
            false
        );
    });

    ['dragleave', 'drop'].forEach(evento => {
        zonaDrop.addEventListener(
            evento,
            () => {
                zonaDrop.classList.remove('zonaDropActiva');
            },
            false
        );
    });

    zonaDrop.addEventListener('drop', function (e) {
        const archivos = e.dataTransfer.files;
        if (archivos.length > 0) {
            subirArchivos(archivos);
        }
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

/*
 * Subir archivos al servidor
 */
async function subirArchivos(archivos) {
    const zonaDrop = document.getElementById('zonaDropMedios');
    zonaDrop.classList.add('subiendo');

    for (const archivo of archivos) {
        const formData = new FormData();
        formData.append('file', archivo);

        try {
            const response = await fetch('/admin/media/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                agregarMedioAGrilla(data.media);
            } else {
                alert('Error al subir ' + archivo.name + ': ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al subir ' + archivo.name);
        }
    }

    zonaDrop.classList.remove('subiendo');
    document.getElementById('inputArchivoOculto').value = '';
}

/*
 * Agregar medio a la grilla dinamicamente
 */
function agregarMedioAGrilla(media) {
    const grilla = document.getElementById('grillaMedios');
    const vacio = grilla.querySelector('.mediosVacio');
    if (vacio) vacio.remove();

    const esImagen = media.mime_type.startsWith('image/');
    const esAudio = media.mime_type.startsWith('audio/');
    const esVideo = media.mime_type.startsWith('video/');
    const nombreOriginal = media.metadata?.original_name ?? 'Archivo';
    const tamano = media.metadata?.size_bytes ?? 0;
    const tamanoFormateado = formatearTamano(tamano);

    let miniaturaHtml = generarMiniaturaHtml(esImagen, esAudio, esVideo, media.full_url, nombreOriginal);

    const item = document.createElement('div');
    item.className = 'itemMedio nuevoMedio';
    item.dataset.id = media.id;
    item.dataset.url = media.full_url;
    item.dataset.mime = media.mime_type;
    item.dataset.nombre = nombreOriginal;
    item.onclick = () => seleccionarMedio(media.id);
    item.innerHTML = `
        <div class="miniaturaContenedor">
            ${miniaturaHtml}
            <div class="overlayMedio">
                <span class="indicadorSeleccion">✓</span>
            </div>
        </div>
        <div class="infoMedio">
            <span class="nombreMedio" title="${nombreOriginal}">
                ${nombreOriginal.substring(0, 25)}${nombreOriginal.length > 25 ? '...' : ''}
            </span>
            <span class="metaMedio">${tamanoFormateado}</span>
        </div>
    `;

    grilla.insertBefore(item, grilla.firstChild);
    setTimeout(() => item.classList.remove('nuevoMedio'), 500);
}

/*
 * Generar HTML de miniatura segun tipo
 */
function generarMiniaturaHtml(esImagen, esAudio, esVideo, url, nombre) {
    if (esImagen) {
        return `<img src="${url}" alt="${nombre}" class="miniaturaMedio">`;
    } else if (esAudio) {
        return '<div class="miniaturaPlaceholder tipoAudio"><span class="iconoTipo">🎵</span></div>';
    } else if (esVideo) {
        return '<div class="miniaturaPlaceholder tipoVideo"><span class="iconoTipo">🎬</span></div>';
    } else {
        return '<div class="miniaturaPlaceholder tipoDocumento"><span class="iconoTipo">📄</span></div>';
    }
}

/*
 * Seleccionar medio y mostrar detalles
 */
function seleccionarMedio(id) {
    document.querySelectorAll('.itemMedio.seleccionado').forEach(el => {
        el.classList.remove('seleccionado');
    });

    const item = document.querySelector(`.itemMedio[data-id="${id}"]`);
    if (item) {
        item.classList.add('seleccionado');
    }

    medioSeleccionadoId = id;
    cargarDetallesMedio(id);
}

/*
 * Cargar detalles via AJAX
 */
async function cargarDetallesMedio(id) {
    try {
        const response = await fetch(`/admin/media/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();

        if (data.success) {
            mostrarDetalles(data.media);
        }
    } catch (error) {
        console.error('Error al cargar detalles:', error);
    }
}

/*
 * Mostrar panel de detalles
 */
function mostrarDetalles(media) {
    const panel = document.getElementById('panelDetallesMedio');
    const preview = document.getElementById('previewDetalles');

    const esImagen = media.mime_type.startsWith('image/');
    const esAudio = media.mime_type.startsWith('audio/');
    const esVideo = media.mime_type.startsWith('video/');

    preview.innerHTML = generarPreviewHtml(esImagen, esAudio, esVideo, media.full_url);

    document.getElementById('detalleMediaId').value = media.id;
    document.getElementById('detalleTitulo').value = media.metadata?.title ?? '';
    document.getElementById('detalleAltText').value = media.metadata?.alt_text ?? '';
    document.getElementById('detalleDescripcion').value = media.metadata?.description ?? '';
    document.getElementById('detalleNombre').textContent = media.metadata?.original_name ?? basename(media.path);
    document.getElementById('detalleTipo').textContent = media.mime_type;
    document.getElementById('detalleTamano').textContent = formatearTamano(media.metadata?.size_bytes ?? 0);
    document.getElementById('detalleFecha').textContent = new Date(media.created_at).toLocaleDateString('es');
    document.getElementById('detalleUrl').value = media.full_url;

    panel.style.display = 'block';
}

/*
 * Generar HTML de preview segun tipo
 */
function generarPreviewHtml(esImagen, esAudio, esVideo, url) {
    if (esImagen) {
        return `<img src="${url}" alt="Preview">`;
    } else if (esAudio) {
        return `<audio controls src="${url}"></audio>`;
    } else if (esVideo) {
        return `<video controls src="${url}"></video>`;
    } else {
        return '<div class="previewPlaceholder">📄</div>';
    }
}

/*
 * Cerrar panel de detalles
 */
function cerrarDetalles() {
    document.getElementById('panelDetallesMedio').style.display = 'none';
    document.querySelectorAll('.itemMedio.seleccionado').forEach(el => {
        el.classList.remove('seleccionado');
    });
    medioSeleccionadoId = null;
}

/*
 * Configurar formulario de detalles
 */
function configurarFormularioDetalles() {
    const form = document.getElementById('formDetallesMedio');

    form?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id = document.getElementById('detalleMediaId').value;
        const formData = new FormData(this);

        try {
            const response = await fetch(`/admin/media/${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(formData).toString()
            });

            const data = await response.json();
            if (data.success) {
                alert('Cambios guardados');
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar');
        }
    });
}

/*
 * Copiar URL al portapapeles
 */
function copiarUrl() {
    const input = document.getElementById('detalleUrl');
    input.select();
    document.execCommand('copy');
    alert('URL copiada al portapapeles');
}

/*
 * Confirmar eliminacion de medio
 */
function confirmarEliminarMedio() {
    document.getElementById('modalEliminarMedio').style.display = 'flex';
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminarMedio').style.display = 'none';
}

/*
 * Configurar modal de eliminacion
 */
function configurarModalEliminar() {
    const botonConfirmar = document.getElementById('botonConfirmarEliminarMedio');

    botonConfirmar?.addEventListener('click', async function () {
        if (!medioSeleccionadoId) return;

        try {
            const response = await fetch(`/admin/media/${medioSeleccionadoId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            if (data.success) {
                const item = document.querySelector(`.itemMedio[data-id="${medioSeleccionadoId}"]`);
                if (item) item.remove();
                cerrarDetalles();
                cerrarModalEliminar();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar');
        }
    });
}

/*
 * Configurar toggle de vista grilla/lista
 */
function configurarVistaToggle() {
    document.getElementById('botonVistaGrilla')?.addEventListener('click', function () {
        document.getElementById('grillaMedios').className = 'grillaMedios vistaGrilla';
    });

    document.getElementById('botonVistaLista')?.addEventListener('click', function () {
        document.getElementById('grillaMedios').className = 'grillaMedios vistaLista';
    });
}

/*
 * Configurar eventos de teclado y cierre de modal
 */
function configurarTecladoYModal() {
    document.getElementById('modalEliminarMedio')?.addEventListener('click', function (e) {
        if (e.target === this) cerrarModalEliminar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarDetalles();
            cerrarModalEliminar();
        }
    });
}

/*
 * Utilidades
 */
function basename(path) {
    return path.split('/').pop();
}

function formatearTamano(bytes) {
    if (bytes > 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    return (bytes / 1024).toFixed(2) + ' KB';
}

/* Inicializar cuando el DOM este listo */
document.addEventListener('DOMContentLoaded', inicializarMedios);
