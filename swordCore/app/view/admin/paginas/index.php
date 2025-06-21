<?php
// 1. Define el título de la página.
$tituloPagina = 'Gestión de Páginas';

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
            <a href="/panel/paginas/create" class="btnCrear">
                Añadir
            </a>
        </div>
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

        <div class="listaContenido">
            <?php
            // Se comprueba si la colección de páginas no está vacía.
            if (!$paginas->isEmpty()):
                foreach ($paginas as $pagina):
            ?>
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
                                <span><?php echo htmlspecialchars($pagina->autor->nombre ?? 'Wan'); ?></span>
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
                <?php
                endforeach;
            else: // Si no hay páginas que mostrar
                ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron páginas.
                </div>
            <?php endif; ?>
        </div>

        <div class="paginacion">
            <?php
            // Las variables $paginaActual y $totalPaginas son pasadas desde el controlador.
            echo renderizarPaginacion($paginaActual, $totalPaginas);
            ?>
        </div>
    </div>
</div>

<?php 
?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>