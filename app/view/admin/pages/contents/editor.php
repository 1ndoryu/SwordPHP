<?php
$isEdit = $mode === 'edit';
$title = $isEdit ? ($contentItem->content_data['title'] ?? '') : '';
$body = $isEdit ? ($contentItem->content_data['content'] ?? '') : '';
$slug = $isEdit ? $contentItem->slug : '';
$status = $isEdit ? $contentItem->status : 'draft';
$contentId = $isEdit ? $contentItem->id : null;

// Configuracion del Post Type
$baseUrl = $baseUrl ?? '/admin/contents';
$postType = $postType ?? 'post';
$postTypeConfig = $postTypeConfig ?? null;
$nombreSingular = $postTypeConfig['nombreSingular'] ?? 'Contenido';

// Obtener metadatos adicionales (excluyendo title y content)
$metadatos = [];
if ($isEdit && is_array($contentItem->content_data)) {
    foreach ($contentItem->content_data as $key => $value) {
        if (!in_array($key, ['title', 'content'])) {
            $metadatos[$key] = $value;
        }
    }
}
?>

<div id="editorContenido" class="contenedorEditor">
    <?php if (isset($saved) && $saved): ?>
        <div class="alertaExito">
            <?= $nombreSingular ?> guardado correctamente.
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?= $isEdit ? $baseUrl . '/' . $contentId : $baseUrl ?>"
        class="formularioEditor"
        id="formularioEditor">
        <input type="hidden" name="_method" value="<?= $isEdit ? 'PUT' : 'POST' ?>">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

        <div class="editorGrid">
            <!-- Columna principal -->
            <div class="editorPrincipal">
                <!-- Titulo -->
                <div class="grupoFormulario">
                    <label for="inputTitulo" class="etiquetaCampo">Titulo</label>
                    <input
                        type="text"
                        id="inputTitulo"
                        name="title"
                        value="<?= htmlspecialchars($title) ?>"
                        class="inputTitulo"
                        placeholder="Escribe el titulo aqui..."
                        required>
                </div>

                <!-- Slug -->
                <div class="grupoFormulario grupoSlug">
                    <label for="inputSlug" class="etiquetaCampo">
                        Slug (URL)
                        <span class="ayudaCampo">Se genera automaticamente si lo dejas vacio</span>
                    </label>
                    <div class="previewSlug">
                        <span class="prefijoSlug"><?= rtrim($_SERVER['HTTP_HOST'] ?? 'localhost', '/') ?>/</span>
                        <input
                            type="text"
                            id="inputSlug"
                            name="slug"
                            value="<?= htmlspecialchars($slug) ?>"
                            class="inputSlug"
                            placeholder="mi-contenido">
                    </div>
                </div>

                <!-- Contenido -->
                <div class="grupoFormulario">
                    <label for="inputContenido" class="etiquetaCampo">Contenido</label>
                    <textarea
                        id="inputContenido"
                        name="content"
                        class="textareaContenido"
                        placeholder="Escribe el contenido aqui..."
                        rows="15"><?= htmlspecialchars($body) ?></textarea>
                </div>

                <!-- Seccion de Metadatos -->
                <div class="seccionMetadatos" id="seccionMetadatos">
                    <div class="encabezadoMetadatos">
                        <h3 class="tituloSeccion">Metadatos</h3>
                        <button type="button" class="botonAgregarMeta" id="botonAgregarMeta">
                            + Agregar campo
                        </button>
                    </div>

                    <div class="listaMetadatos" id="listaMetadatos">
                        <?php if (count($metadatos) > 0): ?>
                            <?php foreach ($metadatos as $key => $value): ?>
                                <div class="filaMetadato" data-key="<?= htmlspecialchars($key) ?>">
                                    <div class="campoMetaClave">
                                        <input
                                            type="text"
                                            class="inputMetaClave"
                                            name="meta_keys[]"
                                            value="<?= htmlspecialchars($key) ?>"
                                            placeholder="Clave"
                                            readonly>
                                    </div>
                                    <div class="campoMetaValor">
                                        <?php if (is_array($value)): ?>
                                            <textarea
                                                class="inputMetaValor textareaMetaValor"
                                                name="meta_values[]"
                                                placeholder="Valor (JSON)"><?= htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>
                                            <input type="hidden" name="meta_is_json[]" value="1">
                                        <?php else: ?>
                                            <input
                                                type="text"
                                                class="inputMetaValor"
                                                name="meta_values[]"
                                                value="<?= htmlspecialchars((string)$value) ?>"
                                                placeholder="Valor">
                                            <input type="hidden" name="meta_is_json[]" value="0">
                                        <?php endif; ?>
                                    </div>
                                    <div class="accionesMetadato">
                                        <button type="button" class="botonEditarClave" title="Editar clave" onclick="editarClave(this)">
                                            Editar
                                        </button>
                                        <button type="button" class="botonEliminarMeta" title="Eliminar" onclick="eliminarMetadato(this)">
                                            X
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="mensajeSinMetadatos" id="mensajeSinMetadatos">
                                <p>No hay metadatos adicionales. Haz clic en "Agregar campo" para crear uno.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel lateral -->
            <div class="editorLateral">
                <!-- Publicacion -->
                <div class="panelLateral">
                    <h3 class="tituloPanelLateral">Publicacion</h3>
                    <div class="contenidoPanelLateral">
                        <div class="grupoFormulario">
                            <label for="selectEstado" class="etiquetaCampo">Estado</label>
                            <select id="selectEstado" name="status" class="selectEstado">
                                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publicado</option>
                            </select>
                        </div>

                        <?php if ($isEdit): ?>
                            <div class="infoPublicacion">
                                <p><strong>Tipo:</strong> <?= ucfirst($type) ?></p>
                                <p><strong>ID:</strong> <?= $contentItem->id ?></p>
                                <p><strong>Creado:</strong> <?= $contentItem->created_at->format('d/m/Y H:i') ?></p>
                                <p><strong>Actualizado:</strong> <?= $contentItem->updated_at->format('d/m/Y H:i') ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="accionesPanelLateral">
                            <button type="submit" class="botonPrimario botonGuardar">
                                <?= $isEdit ? 'Actualizar' : 'Publicar' ?>
                            </button>
                            <button type="submit" name="status" value="draft" class="botonSecundario botonBorrador">
                                Guardar borrador
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Imagen Destacada -->
                <?php
                $imagenDestacada = $isEdit ? ($contentItem->content_data['featured_image'] ?? null) : null;
                $imagenUrl = $imagenDestacada ? ($imagenDestacada['url'] ?? '') : '';
                ?>
                <div class="panelLateral">
                    <h3 class="tituloPanelLateral">Imagen destacada</h3>
                    <div class="contenidoPanelLateral">
                        <div class="campoImagenDestacada" id="campoImagenDestacada">
                            <div class="previewImagenDestacada" id="previewImagenDestacada">
                                <?php if ($imagenUrl): ?>
                                    <img src="<?= htmlspecialchars($imagenUrl) ?>" alt="Imagen destacada">
                                <?php else: ?>
                                    <div class="placeholderImagen">
                                        <span class="iconoPlaceholder">🖼️</span>
                                        <span>Sin imagen</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="accionesImagenDestacada">
                                <button type="button" class="botonSecundario" id="botonSeleccionarImagen">
                                    <?= $imagenUrl ? 'Cambiar' : 'Seleccionar' ?>
                                </button>
                                <button type="button" class="botonSecundario" id="botonQuitarImagen" style="<?= $imagenUrl ? '' : 'display:none' ?>">
                                    Quitar
                                </button>
                            </div>
                            <input type="hidden" name="featured_image_id" id="inputImagenDestacadaId" value="<?= htmlspecialchars($imagenDestacada['id'] ?? '') ?>">
                            <input type="hidden" name="featured_image_url" id="inputImagenDestacadaUrl" value="<?= htmlspecialchars($imagenUrl) ?>">
                        </div>
                    </div>
                </div>

                <!-- Vista previa -->
                <?php if ($isEdit): ?>
                    <div class="panelLateral">
                        <h3 class="tituloPanelLateral">Vista Previa</h3>
                        <div class="contenidoPanelLateral">
                            <a href="/<?= htmlspecialchars($slug) ?>" target="_blank" class="botonSecundario botonVistaPrevia" data-no-spa>
                                Ver en el sitio
                            </a>
                        </div>
                    </div>

                    <!-- Datos crudos -->
                    <div class="panelLateral">
                        <h3 class="tituloPanelLateral">Datos JSON</h3>
                        <div class="contenidoPanelLateral">
                            <details class="detallesJson">
                                <summary>Ver content_data completo</summary>
                                <pre class="preJsonData"><?= htmlspecialchars(json_encode($contentItem->content_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </details>
                        </div>
                    </div>

                    <!-- Zona de Peligro -->
                    <div class="panelLateral panelPeligro">
                        <h3 class="tituloPanelLateral tituloPeligro">Zona de Peligro</h3>
                        <div class="contenidoPanelLateral">
                            <p class="advertenciaPeligro">Esta accion no se puede deshacer.</p>
                            <button
                                type="button"
                                class="botonPeligro botonEliminarContenido"
                                id="botonEliminarContenido"
                                data-base-url="<?= $baseUrl ?>"
                                data-content-id="<?= $contentId ?>">
                                Mover a papelera
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<?= \app\services\AssetManager::js('admin/js/editor.js') ?>