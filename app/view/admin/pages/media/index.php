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

    <!-- Paginacion (componente reutilizable) -->
    <?php
    echo render_view('admin/components/paginacion', [
        'baseUrl' => '/admin/media',
        'paginaActual' => $currentPage,
        'totalPaginas' => $totalPages,
        'filtros' => $filters,
        'idContenedor' => 'paginacionMedios'
    ]);
    ?>
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

<!-- Script de medios -->
<?= \app\services\AssetManager::js('admin/js/medios.js') ?>