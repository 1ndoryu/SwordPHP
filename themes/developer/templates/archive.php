<?php

/**
 * Plantilla para archivo/listado de posts
 * 
 * Este archivo es similar a index.php pero específico para archivos.
 * 
 * @package Developer Theme
 */

obtenerCabecera();
?>

<div class="contenedor">
    <section id="archivo-posts" class="archivoSeccion">
        <header class="encabezadoArchivo">
            <h1 class="tituloArchivo">
                <?php
                if (isset($tipo) && $tipo !== 'post') {
                    echo ucfirst($tipo);
                } else {
                    echo 'Blog';
                }
                ?>
            </h1>
            <p class="subtituloArchivo">
                <?php echo $total; ?> publicaciones encontradas
            </p>
        </header>

        <?php if (!empty($posts)): ?>
            <div class="listadoArchivo">
                <?php foreach ($posts as $post): ?>
                    <?php $GLOBALS['contenido'] = $post; ?>
                    <article id="post-<?php echo $post['id']; ?>" class="itemArchivo">
                        <?php if (tieneImagen()): ?>
                            <div class="itemArchivo__imagen">
                                <a href="<?php elEnlace(); ?>">
                                    <?php laImagen('miniatura'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="itemArchivo__contenido">
                            <h2 class="itemArchivo__titulo">
                                <a href="<?php elEnlace(); ?>">
                                    <?php elTitulo(); ?>
                                </a>
                            </h2>

                            <time class="itemArchivo__fecha" datetime="<?php echo obtenerFecha('Y-m-d'); ?>">
                                <?php laFecha('d M, Y'); ?>
                            </time>

                            <p class="itemArchivo__extracto">
                                <?php elExcerpto(200); ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <nav id="paginacion-archivo" class="paginacion">
                    <?php if ($paginaActual > 1): ?>
                        <a href="?pagina=<?php echo $paginaActual - 1; ?>" class="paginacion__enlace">
                            &larr; Anterior
                        </a>
                    <?php endif; ?>

                    <span class="paginacion__info">
                        Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?>
                    </span>

                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?pagina=<?php echo $paginaActual + 1; ?>" class="paginacion__enlace">
                            Siguiente &rarr;
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="mensajeVacio">
                <p>No hay contenido disponible en esta sección.</p>
                <a href="<?php echo urlInicio(); ?>">Volver al inicio</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php obtenerPie(); ?>