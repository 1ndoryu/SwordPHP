<?php

/**
 * Plantilla para páginas estáticas
 * 
 * @package Developer Theme
 */

obtenerCabecera();
?>

<article id="pagina-<?php echo $contenido['id']; ?>" class="contenidoPagina">
    <div class="contenedor contenedorPagina">
        <header class="encabezadoPagina">
            <h1 class="tituloPagina"><?php elTitulo(); ?></h1>
        </header>

        <?php if (tieneImagen()): ?>
            <figure class="imagenDestacadaPagina">
                <?php laImagen('imagenPaginaPrincipal'); ?>
            </figure>
        <?php endif; ?>

        <div class="cuerpoPagina">
            <?php elContenido(); ?>
        </div>
    </div>
</article>

<?php obtenerPie(); ?>