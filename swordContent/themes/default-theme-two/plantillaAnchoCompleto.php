<?php

/**
 * Template Name: Ancho Completo
 *
 * Esta es una plantilla de página de ejemplo de ancho completo.
 * Muestra el contenido de la página en un contenedor más amplio.
 */

// 1. Define el título que usará el header.php.
// La variable $pagina es pasada desde el controlador.
$titulo = $pagina->titulo;

getHeader();
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 2rem; background: #fff; border: 1px solid #eee;">

    <h1><?php echo htmlspecialchars($pagina->titulo); ?></h1>

    <hr style="margin: 1.5rem 0;">

    <div class="contenido-ancho-completo">
        <?php
        // NOTA DE SEGURIDAD: Se usa 'echo' directamente para renderizar HTML.
        // Asegúrate de que el contenido se sanitiza ANTES de guardarlo en la base de datos.
        echo $pagina->contenido;
        ?>
    </div>

    <hr style="margin: 1.5rem 0;">

    <p style="background-color: #e7f3fe; border: 1px solid #b3d7ff; padding: 10px; border-radius: 4px;">
        ✅ Plantilla de página cargada desde: <code>plantilla-ancho-completo.php</code>
    </p>

</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<?php
getFooter();
?>