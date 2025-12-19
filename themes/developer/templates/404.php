<?php

/**
 * Plantilla 404 - Página no encontrada
 * 
 * @package Developer Theme
 */

obtenerCabecera();
?>

<section id="pagina-404" class="seccion404">
    <div class="contenedor contenedor404">
        <div class="contenido404">
            <h1 class="codigo404">404</h1>
            <h2 class="titulo404">Página no encontrada</h2>
            <p class="mensaje404">
                Lo sentimos, la página que buscas no existe o ha sido movida.
            </p>
            <a href="<?php echo urlInicio(); ?>" class="boton404">
                Volver al inicio
            </a>
        </div>
    </div>
</section>

<?php obtenerPie(); ?>