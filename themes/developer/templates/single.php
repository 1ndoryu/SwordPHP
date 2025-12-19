<?php

/**
 * Plantilla para contenido individual (posts)
 * 
 * @package Developer Theme
 */

obtenerCabecera();
?>

<article id="post-<?php echo $contenido['id']; ?>" class="contenidoSingle">
    <div class="contenedor contenedorArticulo">
        <header class="encabezadoArticulo">
            <h1 class="tituloArticulo"><?php elTitulo(); ?></h1>

            <div class="metaArticulo">
                <time datetime="<?php echo obtenerFecha('Y-m-d'); ?>">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <?php laFecha('d \d\e F, Y'); ?>
                </time>
            </div>
        </header>

        <?php if (tieneImagen()): ?>
            <figure class="imagenDestacadaSingle">
                <?php laImagen('imagenArticuloPrincipal'); ?>
            </figure>
        <?php endif; ?>

        <div class="cuerpoArticulo">
            <?php elContenido(); ?>
        </div>

        <footer class="pieArticulo">
            <a href="<?php echo urlInicio(); ?>" class="enlaceVolver">
                &larr; Volver al inicio
            </a>
        </footer>
    </div>
</article>

<?php obtenerPie(); ?>