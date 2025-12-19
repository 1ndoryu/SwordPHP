<?php

/**
 * Plantilla principal - Index
 * 
 * Esta es la plantilla de fallback para cualquier contenido.
 * 
 * @package Developer Theme
 */

obtenerCabecera();
?>

<div class="contenedor">
    <section id="listado-posts" class="listadoPosts">
        <h1 class="tituloSeccion">Últimas Publicaciones</h1>

        <?php if (!empty($posts)): ?>
            <div class="grillaPosts">
                <?php foreach ($posts as $post): ?>
                    <?php
                    /* Establecer el post actual como global para los template tags */
                    $GLOBALS['contenido'] = $post;
                    ?>
                    <article id="post-<?php echo $post['id']; ?>" class="tarjetaPost">
                        <?php if (tieneImagen()): ?>
                            <div class="tarjetaPost__imagen">
                                <a href="<?php elEnlace(); ?>">
                                    <?php laImagen('imagenDestacada'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="tarjetaPost__contenido">
                            <h2 class="tarjetaPost__titulo">
                                <a href="<?php elEnlace(); ?>">
                                    <?php elTitulo(); ?>
                                </a>
                            </h2>

                            <div class="tarjetaPost__meta">
                                <time datetime="<?php echo obtenerFecha('Y-m-d'); ?>">
                                    <?php laFecha('d M, Y'); ?>
                                </time>
                            </div>

                            <p class="tarjetaPost__extracto">
                                <?php elExcerpto(120); ?>
                            </p>

                            <a href="<?php elEnlace(); ?>" class="botonLeerMas">
                                Leer más
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <nav id="paginacion" class="paginacion">
                    <?php if ($paginaActual > 1): ?>
                        <a href="?pagina=<?php echo $paginaActual - 1; ?>" class="paginacion__enlace paginacion__anterior">
                            &larr; Anterior
                        </a>
                    <?php endif; ?>

                    <span class="paginacion__info">
                        Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?>
                    </span>

                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?pagina=<?php echo $paginaActual + 1; ?>" class="paginacion__enlace paginacion__siguiente">
                            Siguiente &rarr;
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="mensajeVacio">
                <p>No hay publicaciones disponibles.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php obtenerPie(); ?>