<div id="contenidosListado" class="contenedorListado">
    <!-- Barra de herramientas -->
    <div class="barraHerramientas">
        <div class="barraHerramientasIzquierda">
            <a href="/admin/contents/create" class="botonPrimario botonNuevo">
                + Nuevo Contenido
            </a>
            <button type="button" class="botonSecundario botonEliminarSeleccionados" id="botonEliminarSeleccionados" style="display: none;">
                Eliminar (<span id="contadorSeleccionados">0</span>)
            </button>
            <a href="/admin/contents/trash" class="botonSecundario enlacePapelera">
                Papelera
            </a>
        </div>
        <div class="barraHerramientasDerecha">
            <form method="GET" action="/admin/contents" class="formularioFiltros" id="formularioFiltros">
                <select name="type" class="selectFiltro" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($types as $typeOption): ?>
                        <option value="<?= htmlspecialchars($typeOption) ?>" <?= $filters['type'] === $typeOption ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($typeOption)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="selectFiltro" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                    <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                </select>
                <div class="grupoBusqueda">
                    <input
                        type="text"
                        name="search"
                        placeholder="Buscar por titulo..."
                        value="<?= htmlspecialchars($filters['search']) ?>"
                        class="inputBusqueda">
                    <button type="submit" class="botonBuscar">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="resumenListado">
        <span class="contadorTotal"><?= $total ?> contenido<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?></span>
        <span class="ayudaSeleccion">Ctrl+clic para seleccionar varios</span>
        <?php if (!empty($filters['type']) || !empty($filters['status']) || !empty($filters['search'])): ?>
            <a href="/admin/contents" class="enlaceLimpiarFiltros">Limpiar filtros</a>
        <?php endif; ?>
    </div>

    <!-- Tabla de contenidos -->
    <div class="contenedorTabla">
        <table class="tablaContenidos" id="tablaContenidos">
            <thead>
                <tr>
                    <th class="columnaTitulo">Titulo</th>
                    <th class="columnaTipo">Tipo</th>
                    <th class="columnaEstado">Estado</th>
                    <th class="columnaAutor">Autor</th>
                    <th class="columnaFecha">Fecha</th>
                    <th class="columnaAcciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($contents) > 0): ?>
                    <?php foreach ($contents as $contentItem): ?>
                        <?php
                        $data = $contentItem->content_data;
                        $title = $data['title'] ?? 'Sin titulo';
                        $authorName = $contentItem->user ? $contentItem->user->username : 'Desconocido';
                        ?>
                        <tr data-id="<?= $contentItem->id ?>" class="filaContenido" onclick="manejarClicFila(event, <?= $contentItem->id ?>)">
                            <td class="columnaTitulo">
                                <span class="enlaceTitulo">
                                    <?= htmlspecialchars($title) ?>
                                </span>
                                <span class="slugContenido">/<?= htmlspecialchars($contentItem->slug) ?></span>
                            </td>
                            <td class="columnaTipo">
                                <span class="etiquetaTipo etiquetaTipo<?= ucfirst($contentItem->type) ?>">
                                    <?= ucfirst($contentItem->type) ?>
                                </span>
                            </td>
                            <td class="columnaEstado">
                                <?php if ($contentItem->status === 'published'): ?>
                                    <span class="etiquetaEstado estadoPublicado">Publicado</span>
                                <?php else: ?>
                                    <span class="etiquetaEstado estadoBorrador">Borrador</span>
                                <?php endif; ?>
                            </td>
                            <td class="columnaAutor"><?= htmlspecialchars($authorName) ?></td>
                            <td class="columnaFecha">
                                <?= $contentItem->created_at->format('d/m/Y H:i') ?>
                            </td>
                            <td class="columnaAcciones">
                                <div class="grupoAcciones">
                                    <a href="/<?= $contentItem->slug ?>" target="_blank" class="botonAccion botonVer" title="Ver" data-no-spa>
                                        Ver
                                    </a>
                                    <button
                                        type="button"
                                        class="botonAccion botonEliminar"
                                        title="Eliminar"
                                        onclick="event.stopPropagation(); confirmarEliminar(<?= $contentItem->id ?>)">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="celdaVacia">
                            <div class="mensajeVacio">
                                <p>No se encontraron contenidos</p>
                                <a href="/admin/contents/create" class="botonPrimario">Crear el primero</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginacion -->
    <?php if ($totalPages > 1): ?>
        <div class="paginacion" id="paginacion">
            <?php
            $baseUrl = '/admin/contents?';
            $queryParams = [];
            if (!empty($filters['type'])) $queryParams[] = 'type=' . urlencode($filters['type']);
            if (!empty($filters['status'])) $queryParams[] = 'status=' . urlencode($filters['status']);
            if (!empty($filters['search'])) $queryParams[] = 'search=' . urlencode($filters['search']);
            $baseUrl .= implode('&', $queryParams);
            if (!empty($queryParams)) $baseUrl .= '&';
            ?>

            <?php if ($currentPage > 1): ?>
                <a href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>" class="botonPagina">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="botonPagina paginaActual"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>page=<?= $i ?>" class="botonPagina"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>" class="botonPagina">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmacion de eliminacion -->
<div id="modalEliminar" class="modalOverlay" style="display: none;">
    <div class="modalContenido">
        <h3>Enviar a papelera</h3>
        <p id="mensajeModalEliminar">El contenido sera enviado a la papelera. Podras restaurarlo mas tarde.</p>
        <div class="modalAcciones">
            <button type="button" class="botonSecundario" onclick="cerrarModalEliminar()">Cancelar</button>
            <button type="button" class="botonPeligro" id="botonConfirmarEliminar">Eliminar</button>
        </div>
    </div>
</div>

<script>
    // Estado de seleccion
    let filasSeleccionadas = new Set();
    let idAEliminar = null;
    let eliminarMultiples = false;

    // Manejar clic en fila - Ctrl/Alt para seleccion multiple
    function manejarClicFila(event, id) {
        const fila = event.currentTarget;

        // Si se presiono Ctrl o Alt, alternar seleccion
        if (event.ctrlKey || event.altKey) {
            event.preventDefault();
            toggleSeleccion(fila, id);
        } else {
            // Clic normal - navegar al editor via SPA
            limpiarSeleccion();
            if (window.SPA) {
                window.SPA.navegar('/admin/contents/' + id + '/edit');
            } else {
                window.location.href = '/admin/contents/' + id + '/edit';
            }
        }
    }

    function toggleSeleccion(fila, id) {
        if (filasSeleccionadas.has(id)) {
            filasSeleccionadas.delete(id);
            fila.classList.remove('filaSeleccionada');
        } else {
            filasSeleccionadas.add(id);
            fila.classList.add('filaSeleccionada');
        }
        actualizarBotonEliminarMultiples();
    }

    function limpiarSeleccion() {
        filasSeleccionadas.clear();
        document.querySelectorAll('.filaSeleccionada').forEach(fila => {
            fila.classList.remove('filaSeleccionada');
        });
        actualizarBotonEliminarMultiples();
    }

    function actualizarBotonEliminarMultiples() {
        const boton = document.getElementById('botonEliminarSeleccionados');
        const contador = document.getElementById('contadorSeleccionados');

        if (filasSeleccionadas.size > 0) {
            boton.style.display = 'inline-block';
            contador.textContent = filasSeleccionadas.size;
        } else {
            boton.style.display = 'none';
        }
    }

    // Boton eliminar multiples
    document.getElementById('botonEliminarSeleccionados')?.addEventListener('click', function() {
        if (filasSeleccionadas.size > 0) {
            eliminarMultiples = true;
            document.getElementById('mensajeModalEliminar').textContent =
                'Se enviaran ' + filasSeleccionadas.size + ' contenido(s) a la papelera. Podras restaurarlos mas tarde.';
            document.getElementById('modalEliminar').style.display = 'flex';
        }
    });

    function confirmarEliminar(id) {
        idAEliminar = id;
        eliminarMultiples = false;
        document.getElementById('mensajeModalEliminar').textContent =
            'El contenido sera enviado a la papelera. Podras restaurarlo mas tarde.';
        document.getElementById('modalEliminar').style.display = 'flex';
    }

    function cerrarModalEliminar() {
        idAEliminar = null;
        eliminarMultiples = false;
        document.getElementById('modalEliminar').style.display = 'none';
    }

    document.getElementById('botonConfirmarEliminar')?.addEventListener('click', function() {
        if (eliminarMultiples && filasSeleccionadas.size > 0) {
            // Eliminar multiples
            const promesas = Array.from(filasSeleccionadas).map(id =>
                fetch('/admin/contents/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
            );

            Promise.all(promesas).then(() => {
                filasSeleccionadas.forEach(id => {
                    const fila = document.querySelector('tr[data-id="' + id + '"]');
                    if (fila) fila.remove();
                });
                limpiarSeleccion();
                cerrarModalEliminar();
            }).catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar algunos contenidos');
            });
        } else if (idAEliminar) {
            // Eliminar uno
            fetch('/admin/contents/' + idAEliminar, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const fila = document.querySelector('tr[data-id="' + idAEliminar + '"]');
                        if (fila) fila.remove();
                        cerrarModalEliminar();
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

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalEliminar')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalEliminar();
        }
    });

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            limpiarSeleccion();
            cerrarModalEliminar();
        }
    });
</script>