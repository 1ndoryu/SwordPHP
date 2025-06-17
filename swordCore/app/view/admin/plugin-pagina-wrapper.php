<?php
// La variable $tituloPagina es pasada desde el PluginPageController.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Ajustes de Plugin']);
?>

<?php // -- COMIENZO DEL CONTENIDO DEL PLUGIN -- 
?>

<div class="wrap">
    <?php
    // La variable $contenidoPaginaPlugin es el HTML generado por el callback del plugin.
    // Usamos echo directamente porque se espera que el callback genere HTML seguro.
    echo $contenidoPaginaPlugin ?? '<p>El plugin no generó ningún contenido para esta página.</p>';
    ?>
</div>

<?php // -- FIN DEL CONTENIDO DEL PLUGIN -- 
?>

<?php
// Incluimos el pie de página del panel.
echo partial('layouts/admin-footer', []);
?>