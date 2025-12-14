<div id="papeleraListado" class="contenedorListado">
    <!-- Barra de herramientas -->
    <div class="barraHerramientas">
        <div class="barraHerramientasIzquierda">
            <a href="/admin/contents" class="botonSecundario">
                Volver a Contenidos
            </a>
            <?php if ($total > 0): ?>
                <button type="button" class="botonPeligro" id="botonVaciarPapelera">
                    Vaciar Papelera
                </button>
            <?php endif; ?>
        </div>
        <div class="barraHerramientasDerecha">
            <form method="GET" action="/admin/contents/trash" class="formularioFiltros" id="formularioFiltrosPapelera">
                <div class="grupoBusqueda">
                    <input
                        type="text"
                        name="search"
                        placeholder="Buscar en papelera..."
                        value="<?= htmlspecialchars($filters['search']) ?>"
                        class="inputBusqueda">
                    <button type="submit" class="botonBuscar">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="resumenListado">
        <span class="contadorTotal"><?= $total ?> contenido<?= $total !== 1 ? 's' : '' ?> en papelera</span>
        <?php if (!empty($filters['search'])): ?>
            <a href="/admin/contents/trash" class="enlaceLimpiarFiltros">Limpiar busqueda</a>
        <?php endif; ?>
    </div>

    <!-- Tabla de contenidos -->
    <div class="contenedorTabla">
        <table class="tablaContenidos" id="tablaPapelera">
            <thead>
                <tr>
                    <th class="columnaTitulo">Titulo</th>
                    <th class="columnaTipo">Tipo</th>
                    <th class="columnaAutor">Autor</th>
                    <th class="columnaFecha">Eliminado</th>
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
                        <tr data-id="<?= $contentItem->id ?>" class="filaContenido">
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
                            <td class="columnaAutor"><?= htmlspecialchars($authorName) ?></td>
                            <td class="columnaFecha">
                                <?= $contentItem->deleted_at->format('d/m/Y H:i') ?>
                            </td>
                            <td class="columnaAcciones">
                                <div class="grupoAcciones">
                                    <button
                                        type="button"
                                        class="botonAccion botonRestaurar"
                                        title="Restaurar"
                                        onclick="restaurarContenido(<?= $contentItem->id ?>)">
                                        Restaurar
                                    </button>
                                    <button
                                        type="button"
                                        class="botonAccion botonEliminar"
                                        title="Eliminar permanentemente"
                                        onclick="confirmarEliminarPermanente(<?= $contentItem->id ?>)">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="celdaVacia">
                            <div class="mensajeVacio">
                                <p>La papelera esta vacia</p>
                                <a href="/admin/contents" class="botonPrimario">Volver a contenidos</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginacion -->
    <?php if ($totalPages > 1): ?>
        <div class="paginacion" id="paginacionPapelera">
            <?php
            $baseUrl = '/admin/contents/trash?';
            $queryParams = [];
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

<!-- Modal de confirmacion de eliminacion permanente -->
<div id="modalEliminarPermanente" class="modalOverlay" style="display: none;">
    <div class="modalContenido">
        <h3>Eliminar permanentemente</h3>
        <p id="mensajeModalEliminar">Esta accion eliminara el contenido de forma permanente. No se puede deshacer.</p>
        <div class="modalAcciones">
            <button type="button" class="botonSecundario" onclick="cerrarModalEliminar()">Cancelar</button>
            <button type="button" class="botonPeligro" id="botonConfirmarEliminarPermanente">Eliminar</button>
        </div>
    </div>
</div>

<!-- Modal de confirmacion para vaciar papelera -->
<div id="modalVaciarPapelera" class="modalOverlay" style="display: none;">
    <div class="modalContenido">
        <h3>Vaciar papelera</h3>
        <p>Esta accion eliminara <?= $total ?> contenido(s) de forma permanente. No se puede deshacer.</p>
        <div class="modalAcciones">
            <button type="button" class="botonSecundario" onclick="cerrarModalVaciar()">Cancelar</button>
            <button type="button" class="botonPeligro" id="botonConfirmarVaciar">Vaciar</button>
        </div>
    </div>
</div>

<script>
    let idAEliminar = null;

    // Restaurar contenido
    function restaurarContenido(id) {
        fetch('/admin/contents/' + id + '/restore', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fila = document.querySelector('tr[data-id="' + id + '"]');
                    if (fila) {
                        fila.style.transition = 'opacity 0.3s';
                        fila.style.opacity = '0';
                        setTimeout(() => fila.remove(), 300);
                    }
                } else {
                    alert('Error: ' + (data.message || 'Error al restaurar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al restaurar el contenido');
            });
    }

    // Confirmar eliminacion permanente
    function confirmarEliminarPermanente(id) {
        idAEliminar = id;
        document.getElementById('modalEliminarPermanente').style.display = 'flex';
    }

    function cerrarModalEliminar() {
        idAEliminar = null;
        document.getElementById('modalEliminarPermanente').style.display = 'none';
    }

    document.getElementById('botonConfirmarEliminarPermanente')?.addEventListener('click', function() {
        if (idAEliminar) {
            fetch('/admin/contents/' + idAEliminar + '/force', {
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
                        alert('Error: ' + (data.message || 'Error al eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el contenido');
                });
        }
    });

    // Vaciar papelera
    document.getElementById('botonVaciarPapelera')?.addEventListener('click', function() {
        document.getElementById('modalVaciarPapelera').style.display = 'flex';
    });

    function cerrarModalVaciar() {
        document.getElementById('modalVaciarPapelera').style.display = 'none';
    }

    document.getElementById('botonConfirmarVaciar')?.addEventListener('click', function() {
        fetch('/admin/contents/trash/empty', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la pagina
                    if (window.SPA) {
                        window.SPA.navegar('/admin/contents/trash');
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('Error: ' + (data.message || 'Error al vaciar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al vaciar la papelera');
            });
    });

    // Cerrar modales al hacer clic fuera
    document.getElementById('modalEliminarPermanente')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEliminar();
    });

    document.getElementById('modalVaciarPapelera')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalVaciar();
    });

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalEliminar();
            cerrarModalVaciar();
        }
    });
</script>