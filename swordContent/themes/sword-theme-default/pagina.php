<?php
// 1. Define el título que usará el header.php.
// La variable $pagina es pasada desde el controlador.
$titulo = $pagina->titulo;
# require_once __DIR__ . '/../../swordCore/start.php';

getHeader();
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<h1><?php echo htmlspecialchars($pagina->titulo); ?></h1> 
<div>
    <?php
    // NOTA DE SEGURIDAD: Se usa 'echo' directamente para renderizar HTML.
    // Esto es necesario para contenido de editores de texto enriquecido (WYSIWYG).
    // Asegúrate de que el contenido se sanitiza ANTES de guardarlo en la base de datos.
    echo $pagina->contenido;
    ?>
</div>
<hr>
<p>✅ Vista cargada desde: sword-theme-default</p>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php

gerFooter();
?>