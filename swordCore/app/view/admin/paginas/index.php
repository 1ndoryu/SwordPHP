<?php
// 1. Define el título de la página.
$tituloPagina = 'Gestión de Páginas';

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', []);
?>

<div class="vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
            <a href="/panel/paginas/create" class="btnCrear">
                Añadir
            </a>
        </div>
    </div>

    <?php // Bloque para mostrar mensajes de éxito o error 
    ?>
    <?php if (session()->has('success')): ?>
        <div class="alerta alertaExito" role="alert">
            <?php echo htmlspecialchars(session('success')); ?>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alerta alertaError" role="alert">
            <?php echo htmlspecialchars(session('error')); ?>
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
                            <a href="/panel/paginas/edit/<?php echo htmlspecialchars($pagina->id); ?>" class="iconoB btnEditar">
                                <?php echo icon('edit'); ?>
                            </a>

                            <form action="/panel/paginas/destroy/<?php echo htmlspecialchars($pagina->id); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta página?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="iconoB IconoRojo btnEliminar">
                                    <?php echo icon('borrar'); ?>
                                </button>
                            </form>
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

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>