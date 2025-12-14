<?php
$isEdit = $mode === 'edit';
$title = $isEdit ? ($contentItem->content_data['title'] ?? '') : '';
$body = $isEdit ? ($contentItem->content_data['content'] ?? '') : '';
$slug = $isEdit ? $contentItem->slug : '';
$status = $isEdit ? $contentItem->status : 'draft';
$contentId = $isEdit ? $contentItem->id : null;

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
            Contenido guardado correctamente.
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?= $isEdit ? '/admin/contents/' . $contentId : '/admin/contents' ?>"
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
                            <button type="button" class="botonPeligro botonEliminarContenido" id="botonEliminarContenido">
                                Mover a papelera
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
    // Generar slug automaticamente al escribir el titulo
    const inputTitulo = document.getElementById('inputTitulo');
    const inputSlug = document.getElementById('inputSlug');
    let slugEditadoManualmente = inputSlug.value !== '';

    inputSlug.addEventListener('input', function() {
        slugEditadoManualmente = true;
    });

    inputTitulo.addEventListener('input', function() {
        if (!slugEditadoManualmente || inputSlug.value === '') {
            const slug = this.value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .trim();
            inputSlug.value = slug;
        }
    });

    // Funciones para metadatos
    const listaMetadatos = document.getElementById('listaMetadatos');
    const mensajeSinMetadatos = document.getElementById('mensajeSinMetadatos');

    function ocultarMensajeVacio() {
        if (mensajeSinMetadatos) {
            mensajeSinMetadatos.style.display = 'none';
        }
    }

    function mostrarMensajeVacioSiNecesario() {
        const filas = listaMetadatos.querySelectorAll('.filaMetadato');
        if (filas.length === 0 && mensajeSinMetadatos) {
            mensajeSinMetadatos.style.display = 'block';
        }
    }

    document.getElementById('botonAgregarMeta').addEventListener('click', function() {
        ocultarMensajeVacio();

        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'filaMetadato filaMetadatoNueva';
        nuevaFila.innerHTML = `
            <div class="campoMetaClave">
                <input 
                    type="text" 
                    class="inputMetaClave" 
                    name="meta_keys[]" 
                    placeholder="Clave (ej: autor, precio)"
                    required>
            </div>
            <div class="campoMetaValor">
                <input 
                    type="text" 
                    class="inputMetaValor" 
                    name="meta_values[]"
                    placeholder="Valor">
                <input type="hidden" name="meta_is_json[]" value="0">
            </div>
            <div class="accionesMetadato">
                <button type="button" class="botonConvertirJson" title="Convertir a JSON" onclick="convertirAJson(this)">
                    {}
                </button>
                <button type="button" class="botonEliminarMeta" title="Eliminar" onclick="eliminarMetadato(this)">
                    X
                </button>
            </div>
        `;

        listaMetadatos.appendChild(nuevaFila);
        nuevaFila.querySelector('.inputMetaClave').focus();
    });

    function editarClave(boton) {
        const fila = boton.closest('.filaMetadato');
        const inputClave = fila.querySelector('.inputMetaClave');

        if (inputClave.hasAttribute('readonly')) {
            inputClave.removeAttribute('readonly');
            inputClave.focus();
            inputClave.select();
            boton.textContent = 'OK';
        } else {
            inputClave.setAttribute('readonly', true);
            boton.textContent = 'Editar';
        }
    }

    function eliminarMetadato(boton) {
        const fila = boton.closest('.filaMetadato');
        fila.remove();
        mostrarMensajeVacioSiNecesario();
    }

    function convertirAJson(boton) {
        const fila = boton.closest('.filaMetadato');
        const campoValor = fila.querySelector('.campoMetaValor');
        const inputActual = campoValor.querySelector('.inputMetaValor');
        const valorActual = inputActual.value;

        // Reemplazar input por textarea
        const textarea = document.createElement('textarea');
        textarea.className = 'inputMetaValor textareaMetaValor';
        textarea.name = 'meta_values[]';
        textarea.placeholder = 'Valor (JSON)';

        try {
            // Si ya es JSON valido, formatearlo
            const parsed = JSON.parse(valorActual);
            textarea.value = JSON.stringify(parsed, null, 2);
        } catch (e) {
            // Si no es JSON, crear un objeto con el valor
            textarea.value = valorActual ? JSON.stringify({
                valor: valorActual
            }, null, 2) : '{\n  \n}';
        }

        // Actualizar el campo hidden
        const hiddenIsJson = campoValor.querySelector('input[name="meta_is_json[]"]');
        hiddenIsJson.value = '1';

        inputActual.replaceWith(textarea);
        boton.remove();
    }

    // Boton eliminar contenido
    const botonEliminar = document.getElementById('botonEliminarContenido');
    if (botonEliminar) {
        botonEliminar.addEventListener('click', function() {
            if (confirm('¿Seguro que deseas mover este contenido a la papelera?')) {
                const contentId = <?= $contentId ?? 'null' ?>;
                if (!contentId) return;

                fetch('/admin/contents/' + contentId, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Navegar al listado via SPA
                            if (window.SPA) {
                                window.SPA.navegar('/admin/contents');
                            } else {
                                window.location.href = '/admin/contents';
                            }
                        } else {
                            alert('Error al eliminar: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al eliminar el contenido');
                    });
            }
        });
    }
</script>