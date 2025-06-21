<?php
// 1. Define las variables y el título usando la configuración genérica.
$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['name'] ?? 'Contenidos Genéricos');

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="tituloVista">
            <h1><?php echo $tituloPagina; ?></h1>
        </div>
        <div class="accionesVista">
            <a href="/panel/<?= htmlspecialchars($slug) ?>/crear" class="btnCrear">
                Añadir <?= htmlspecialchars($labels['singular_name'] ?? 'Nuevo') ?>
            </a>
        </div>
    </div>

    <?php // Formulario de Filtros ?>
    <div class="filtrosListado">
        <form action="/panel/<?= htmlspecialchars($slug) ?>" method="GET" id="filtrosFormContenido">
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
                    <?php if (isset($autores)): ?>
                        <?php foreach ($autores as $autor): ?>
                            <option value="<?php echo $autor->id; ?>" <?php echo (($filtrosActuales['author_filter'] ?? '') == $autor->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($autor->nombremostrado ?: $autor->nombreusuario); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="accionesFiltro">
                <button type="submit" class="btnFiltrar">Filtrar</button>
                <a href="/panel/<?= htmlspecialchars($slug) ?>" class="btnLimpiar">Limpiar</a>
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

        <div class="listaContenido" id="listaContenidoContainer">
            <?php if (!$entradas->isEmpty()): ?>
                <?php foreach ($entradas as $entrada): ?>
                    <div class="contenidoCard">
                        <div class="contenidoInfo">
                            <div class="infoItem iconoB iconoG">
                                <?php // Podríamos usar $config['menu_icon'] si está definido y es un helper de icono ?>
                                <?php echo icon(isset($config['menu_icon_svg']) ? $config['menu_icon_svg'] : 'file'); ?>
                            </div>
                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($entrada->titulo); ?></span>
                            </div>
                            <div class="infoItem" style="display: none">
                                <span><?php echo htmlspecialchars($entrada->id); ?></span>
                            </div>
                             <div class="infoItem">
                                <span><?php echo htmlspecialchars($entrada->autor->nombremostrado ?? ($entrada->autor->nombreusuario ?? 'N/A')); ?></span>
                            </div>
                            <div class="infoItem">
                                <?php if ($entrada->estado == 'publicado'): ?>
                                    <span class="badge badgePublicado">Publicado</span>
                                <?php else: ?>
                                    <span class="badge badgeBorrador">Borrador</span>
                                <?php endif; ?>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($entrada->created_at->format('d/m/Y H:i')); ?></span>
                            </div>
                        </div>

                        <div class="contenidoAcciones">
                            <a href="<?php echo getPermalinkPost($entrada); ?>" class="iconoB btnVer" target="_blank" title="Ver">
                                <?php echo icon('ver'); ?>
                            </a>
                            <a href="/panel/<?= htmlspecialchars($slug) ?>/editar/<?php echo htmlspecialchars($entrada->id); ?>" class="iconoB btnEditar" title="Editar">
                                <?php echo icon('edit'); ?>
                            </a>
                            <button type="button" class="iconoB IconoRojo btnEliminar" title="Eliminar" onclick="eliminarRecurso('/panel/<?= htmlspecialchars($slug) ?>/eliminar/<?php echo htmlspecialchars($entrada->id); ?>', '<?php echo csrf_token(); ?>', '¿Estás seguro de que deseas eliminar esta entrada?');">
                                <?php echo icon('borrar'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron <?= htmlspecialchars(strtolower($labels['name'] ?? 'contenidos')) ?> con los filtros aplicados.
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($paginaActual) && isset($totalPaginas) && $totalPaginas > 0): ?>
            <div class="paginacion">
                <?php echo renderizarPaginacion($paginaActual, $totalPaginas, "/panel/" . htmlspecialchars($slug), $filtrosActuales); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>