<?php

/**
 * Vista de la libreria de medios del panel admin.
 * Muestra grilla de archivos con filtros, upload y acciones.
 */
?>
<div id="mediosLibreria" class="contenedorMedios">
    <!-- Barra de herramientas -->
    <div class="barraHerramientas">
        <div class="barraHerramientasIzquierda">
            <button type="button" class="botonPrimario" id="botonSubirArchivo">
                + Subir archivo
            </button>
            <button type="button" class="botonSecundario botonToggleVista" id="botonVistaGrilla" title="Vista grilla">
                ▦
            </button>
            <button type="button" class="botonSecundario botonToggleVista" id="botonVistaLista" title="Vista lista">
                ☰
            </button>
        </div>
        <div class="barraHerramientasDerecha">
            <form method="GET" action="/admin/media" class="formularioFiltros" id="formularioFiltrosMedios">
                <select name="type" class="selectFiltro" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <option value="image" <?= ($filters['type'] ?? '') === 'image' ? 'selected' : '' ?>>Imagenes</option>
                    <option value="audio" <?= ($filters['type'] ?? '') === 'audio' ? 'selected' : '' ?>>Audio</option>
                    <option value="video" <?= ($filters['type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
                    <option value="application" <?= ($filters['type'] ?? '') === 'application' ? 'selected' : '' ?>>Documentos</option>
                </select>
                <div class="grupoBusqueda">
                    <input
                        type="text"
                        name="search"
                        placeholder="Buscar por nombre..."
                        value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                        class="inputBusqueda">
                    <button type="submit" class="botonBuscar">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="resumenListado">
        <span class="contadorTotal"><?= $total ?> archivo<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?></span>
        <?php if (!empty($filters['type']) || !empty($filters['search'])): ?>
            <a href="/admin/media" class="enlaceLimpiarFiltros">Limpiar filtros</a>
        <?php endif; ?>
    </div>

    <!-- Zona de drop para upload -->
    <div id="zonaDropMedios" class="zonaDropMedios">
        <div class="zonaDropContenido">
            <span class="zonaDropIcono">📁</span>
            <p>Arrastra archivos aqui o haz clic en "Subir archivo"</p>
        </div>
    </div>

    <!-- Grilla de medios -->
    <div id="grillaMedios" class="grillaMedios vistaGrilla">
        <?php if (count($medios) > 0): ?>
            <?php foreach ($medios as $medio): ?>
                <?php
                $metadata = $medio->metadata ?? [];
                $nombreOriginal = $metadata['original_name'] ?? basename($medio->path);
                $tamano = $metadata['size_bytes'] ?? 0;
                $tamanoFormateado = $tamano > 1048576
                    ? round($tamano / 1048576, 2) . ' MB'
                    : round($tamano / 1024, 2) . ' KB';
                $esImagen = str_starts_with($medio->mime_type, 'image/');
                $esAudio = str_starts_with($medio->mime_type, 'audio/');
                $esVideo = str_starts_with($medio->mime_type, 'video/');
                ?>
                <div class="itemMedio"
                    data-id="<?= $medio->id ?>"
                    data-url="<?= $medio->full_url ?>"
                    data-mime="<?= $medio->mime_type ?>"
                    data-nombre="<?= htmlspecialchars($nombreOriginal) ?>"
                    onclick="seleccionarMedio(<?= $medio->id ?>)">
                    <div class="miniaturaContenedor">
                        <?php if ($esImagen): ?>
                            <img src="<?= $medio->full_url ?>" alt="<?= htmlspecialchars($nombreOriginal) ?>" class="miniaturaMedio">
                        <?php elseif ($esAudio): ?>
                            <div class="miniaturaPlaceholder tipoAudio">
                                <span class="iconoTipo">🎵</span>
                            </div>
                        <?php elseif ($esVideo): ?>
                            <div class="miniaturaPlaceholder tipoVideo">
                                <span class="iconoTipo">🎬</span>
                            </div>
                        <?php else: ?>
                            <div class="miniaturaPlaceholder tipoDocumento">
                                <span class="iconoTipo">📄</span>
                            </div>
                        <?php endif; ?>
                        <div class="overlayMedio">
                            <span class="indicadorSeleccion">✓</span>
                        </div>
                    </div>
                    <div class="infoMedio">
                        <span class="nombreMedio" title="<?= htmlspecialchars($nombreOriginal) ?>">
                            <?= htmlspecialchars(mb_substr($nombreOriginal, 0, 25)) ?><?= mb_strlen($nombreOriginal) > 25 ? '...' : '' ?>
                        </span>
                        <span class="metaMedio"><?= $tamanoFormateado ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mediosVacio">
                <p>No se encontraron archivos</p>
                <button type="button" class="botonPrimario" onclick="document.getElementById('inputArchivoOculto').click()">
                    Subir el primero
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginacion -->
    <?php if ($totalPages > 1): ?>
        <div class="paginacion" id="paginacionMedios">
            <?php
            $urlPaginacion = '/admin/media?';
            $queryParams = [];
            if (!empty($filters['type'])) $queryParams[] = 'type=' . urlencode($filters['type']);
            if (!empty($filters['search'])) $queryParams[] = 'search=' . urlencode($filters['search']);
            $urlPaginacion .= implode('&', $queryParams);
            if (!empty($queryParams)) $urlPaginacion .= '&';
            ?>

            <?php if ($currentPage > 1): ?>
                <a href="<?= $urlPaginacion ?>page=<?= $currentPage - 1 ?>" class="botonPagina">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="botonPagina paginaActual"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $urlPaginacion ?>page=<?= $i ?>" class="botonPagina"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= $urlPaginacion ?>page=<?= $currentPage + 1 ?>" class="botonPagina">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Panel lateral de detalles -->
<div id="panelDetallesMedio" class="panelDetallesMedio" style="display: none;">
    <div class="encabezadoDetalles">
        <h3>Detalles del archivo</h3>
        <button type="button" class="botonCerrarDetalles" onclick="cerrarDetalles()">×</button>
    </div>
    <div class="contenidoDetalles">
        <div class="previewDetalles" id="previewDetalles"></div>
        <form id="formDetallesMedio" class="formDetallesMedio">
            <input type="hidden" id="detalleMediaId" name="id">

            <div class="grupoFormulario">
                <label for="detalleTitulo">Titulo</label>
                <input type="text" id="detalleTitulo" name="title" class="inputFormulario">
            </div>

            <div class="grupoFormulario">
                <label for="detalleAltText">Texto alternativo</label>
                <input type="text" id="detalleAltText" name="alt_text" class="inputFormulario">
            </div>

            <div class="grupoFormulario">
                <label for="detalleDescripcion">Descripcion</label>
                <textarea id="detalleDescripcion" name="description" class="inputFormulario textareaFormulario" rows="3"></textarea>
            </div>

            <div class="infoArchivoDetalles">
                <p><strong>Nombre:</strong> <span id="detalleNombre"></span></p>
                <p><strong>Tipo:</strong> <span id="detalleTipo"></span></p>
                <p><strong>Tamano:</strong> <span id="detalleTamano"></span></p>
                <p><strong>Subido:</strong> <span id="detalleFecha"></span></p>
            </div>

            <div class="grupoFormulario">
                <label>URL del archivo</label>
                <div class="grupoUrlCopiar">
                    <input type="text" id="detalleUrl" class="inputFormulario" readonly>
                    <button type="button" class="botonSecundario" onclick="copiarUrl()">Copiar</button>
                </div>
            </div>

            <div class="accionesDetalles">
                <button type="submit" class="botonPrimario">Guardar cambios</button>
                <button type="button" class="botonPeligro" onclick="confirmarEliminarMedio()">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<!-- Input oculto para upload -->
<input type="file" id="inputArchivoOculto" style="display: none;" multiple accept="image/*,audio/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx">

<!-- Modal de confirmacion de eliminacion -->
<div id="modalEliminarMedio" class="modalOverlay" style="display: none;">
    <div class="modalContenido">
        <h3>Eliminar archivo</h3>
        <p>Este archivo sera eliminado permanentemente. Esta accion no se puede deshacer.</p>
        <div class="modalAcciones">
            <button type="button" class="botonSecundario" onclick="cerrarModalEliminar()">Cancelar</button>
            <button type="button" class="botonPeligro" id="botonConfirmarEliminarMedio">Eliminar</button>
        </div>
    </div>
</div>

<script>
    let medioSeleccionadoId = null;

    /* Abrir selector de archivos */
    document.getElementById('botonSubirArchivo')?.addEventListener('click', function() {
        document.getElementById('inputArchivoOculto').click();
    });

    /* Manejar seleccion de archivos */
    document.getElementById('inputArchivoOculto')?.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            subirArchivos(e.target.files);
        }
    });

    /* Drag and drop */
    const zonaDrop = document.getElementById('zonaDropMedios');
    const grillaMedios = document.getElementById('grillaMedios');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evento => {
        document.body.addEventListener(evento, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(evento => {
        document.body.addEventListener(evento, () => {
            zonaDrop.classList.add('zonaDropActiva');
        }, false);
    });

    ['dragleave', 'drop'].forEach(evento => {
        zonaDrop.addEventListener(evento, () => {
            zonaDrop.classList.remove('zonaDropActiva');
        }, false);
    });

    zonaDrop.addEventListener('drop', function(e) {
        const archivos = e.dataTransfer.files;
        if (archivos.length > 0) {
            subirArchivos(archivos);
        }
    });

    /* Subir archivos */
    async function subirArchivos(archivos) {
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

    /* Agregar medio a la grilla dinamicamente */
    function agregarMedioAGrilla(media) {
        const grilla = document.getElementById('grillaMedios');
        const vacio = grilla.querySelector('.mediosVacio');
        if (vacio) vacio.remove();

        const esImagen = media.mime_type.startsWith('image/');
        const esAudio = media.mime_type.startsWith('audio/');
        const esVideo = media.mime_type.startsWith('video/');
        const nombreOriginal = media.metadata?.original_name ?? 'Archivo';
        const tamano = media.metadata?.size_bytes ?? 0;
        const tamanoFormateado = tamano > 1048576 ?
            (tamano / 1048576).toFixed(2) + ' MB' :
            (tamano / 1024).toFixed(2) + ' KB';

        let miniaturaHtml = '';
        if (esImagen) {
            miniaturaHtml = `<img src="${media.full_url}" alt="${nombreOriginal}" class="miniaturaMedio">`;
        } else if (esAudio) {
            miniaturaHtml = '<div class="miniaturaPlaceholder tipoAudio"><span class="iconoTipo">🎵</span></div>';
        } else if (esVideo) {
            miniaturaHtml = '<div class="miniaturaPlaceholder tipoVideo"><span class="iconoTipo">🎬</span></div>';
        } else {
            miniaturaHtml = '<div class="miniaturaPlaceholder tipoDocumento"><span class="iconoTipo">📄</span></div>';
        }

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

    /* Seleccionar medio y mostrar detalles */
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

    /* Cargar detalles via AJAX */
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

    /* Mostrar panel de detalles */
    function mostrarDetalles(media) {
        const panel = document.getElementById('panelDetallesMedio');
        const preview = document.getElementById('previewDetalles');

        const esImagen = media.mime_type.startsWith('image/');
        const esAudio = media.mime_type.startsWith('audio/');
        const esVideo = media.mime_type.startsWith('video/');

        if (esImagen) {
            preview.innerHTML = `<img src="${media.full_url}" alt="Preview">`;
        } else if (esAudio) {
            preview.innerHTML = `<audio controls src="${media.full_url}"></audio>`;
        } else if (esVideo) {
            preview.innerHTML = `<video controls src="${media.full_url}"></video>`;
        } else {
            preview.innerHTML = '<div class="previewPlaceholder">📄</div>';
        }

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

    function basename(path) {
        return path.split('/').pop();
    }

    function formatearTamano(bytes) {
        if (bytes > 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        return (bytes / 1024).toFixed(2) + ' KB';
    }

    /* Cerrar panel de detalles */
    function cerrarDetalles() {
        document.getElementById('panelDetallesMedio').style.display = 'none';
        document.querySelectorAll('.itemMedio.seleccionado').forEach(el => {
            el.classList.remove('seleccionado');
        });
        medioSeleccionadoId = null;
    }

    /* Guardar cambios en detalles */
    document.getElementById('formDetallesMedio')?.addEventListener('submit', async function(e) {
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

    /* Copiar URL */
    function copiarUrl() {
        const input = document.getElementById('detalleUrl');
        input.select();
        document.execCommand('copy');
        alert('URL copiada al portapapeles');
    }

    /* Eliminar medio */
    function confirmarEliminarMedio() {
        document.getElementById('modalEliminarMedio').style.display = 'flex';
    }

    function cerrarModalEliminar() {
        document.getElementById('modalEliminarMedio').style.display = 'none';
    }

    document.getElementById('botonConfirmarEliminarMedio')?.addEventListener('click', async function() {
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

    /* Toggle vista grilla/lista */
    document.getElementById('botonVistaGrilla')?.addEventListener('click', function() {
        document.getElementById('grillaMedios').className = 'grillaMedios vistaGrilla';
    });

    document.getElementById('botonVistaLista')?.addEventListener('click', function() {
        document.getElementById('grillaMedios').className = 'grillaMedios vistaLista';
    });

    /* Cerrar modal con click externo */
    document.getElementById('modalEliminarMedio')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEliminar();
    });

    /* Tecla Escape */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarDetalles();
            cerrarModalEliminar();
        }
    });
</script>