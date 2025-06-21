<?php
// 1. Define el título de la página.
$tituloPagina = 'Gestión de Páginas';

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="tituloVista">
            <h1><?php echo $tituloPagina; ?></h1>
        </div>
        <div class="accionesVista">
            <a href="/panel/paginas/create" class="btnCrear">
                Añadir
            </a>
        </div>
    </div>

    <?php // Formulario de Filtros ?>
    <div class="filtrosListado">
        <form action="/panel/paginas" method="GET" id="filtrosFormPaginas">
            <div class="campoFiltro">
                <label for="search_term">Buscar:</label>
                <input type="text" name="search_term" id="search_term" value="<?php echo htmlspecialchars($filtrosActuales['search_term'] ?? ''); ?>" placeholder="Título, contenido...">
            </div>
            <div class="campoFiltro">
                <label for="date_filter">Fecha:</label>
                <input type="date" name="date_filter" id="date_filter" value="<?php echo htmlspecialchars($filtrosActuales['date_filter'] ?? ''); ?>">
            </div>
            <div class="campoFiltro">
                <label for="author_filter">Autor:</label>
                <select name="author_filter" id="author_filter">
                    <option value="">Todos los autores</option>
                    <?php foreach ($autores as $autor): ?>
                        <option value="<?php echo $autor->id; ?>" <?php echo (($filtrosActuales['author_filter'] ?? '') == $autor->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($autor->nombremostrado ?: $autor->nombreusuario); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="accionesFiltro">
                <button type="submit" class="btnFiltrar">Filtrar</button>
                <a href="/panel/paginas" class="btnLimpiar">Limpiar</a>
            </div>
        </form>
    </div>


    <?php // Mostrar mensajes pasados desde el controlador ?>
    <?php if (!empty($successMessage)): ?>
        <div class="alerta alertaExito" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="alerta alertaError" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <div class="contenidoVista">

        <div class="listaContenido" id="listaPaginasContainer">
            <?php if (!$paginas->isEmpty()): ?>
                <?php foreach ($paginas as $pagina): ?>
                    <div class="contenidoCard">
                        <div class="contenidoInfo">
                            <div class="infoItem iconoB iconoG">
                                <?php echo icon('file'); ?>
                            </div>
                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($pagina->titulo); ?></span>
                                <?php if (isset($slugPaginaInicio) && $pagina->slug === $slugPaginaInicio): ?>
                                    <span class="badge-inicio"> - Inicio</span>
                                <?php endif; ?>
                            </div>
                            <div class="infoItem" style="display: none">
                                <span><?php echo htmlspecialchars($pagina->id); ?></span>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($pagina->autor->nombremostrado ?? ($pagina->autor->nombreusuario ?? 'N/A')); ?></span>
                            </div>
                            <div class="infoItem">
                                <?php if ($pagina->estado == 'publicado'): ?>
                                    <span class="badge badgePublicado">Publicado</span>
                                <?php else: ?>
                                    <span class="badge badgeBorrador">Borrador</span>
                                <?php endif; ?>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($pagina->created_at->format('d/m/Y H:i')); ?></span>
                            </div>
                        </div>

                        <div class="contenidoAcciones">
                            <a href="<?php echo getPermalinkPost($pagina); ?>" class="iconoB btnVer" target="_blank" title="Ver">
                                <?php echo icon('ver'); ?>
                            </a>
                            <a href="/panel/paginas/edit/<?php echo htmlspecialchars($pagina->id); ?>" class="iconoB btnEditar" title="Editar">
                                <?php echo icon('edit'); ?>
                            </a>
                            <button type="button" class="iconoB IconoRojo btnEliminar" title="Eliminar" onclick="eliminarRecurso('/panel/paginas/destroy/<?php echo htmlspecialchars($pagina->id); ?>', '<?php echo csrf_token(); ?>', '¿Estás seguro de que deseas eliminar esta página?');">
                                <?php echo icon('borrar'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron páginas con los filtros aplicados.
                </div>
            <?php endif; ?>
        </div>

        <div class="paginacion">
            <?php
            // Renderizar paginación. Asegúrate que renderizarPaginacion pueda manejar los parámetros GET de los filtros.
            // Si renderizarPaginacion no maneja automáticamente los parámetros de query, tendrás que pasarlos.
            // Por ejemplo, podrías necesitar modificar renderizarPaginacion o construir los enlaces aquí.
            // Asumimos que renderizarPaginacion es lo suficientemente inteligente o que los parámetros de filtro se añaden a los enlaces de paginación.
            echo renderizarPaginacion($paginaActual, $totalPaginas, request()->url(), $filtrosActuales);
            ?>
        </div>
    </div>
</div>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>