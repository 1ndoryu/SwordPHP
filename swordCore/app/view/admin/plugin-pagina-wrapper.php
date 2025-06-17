<?php
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Ajustes de Plugin']);
?>

<?php 
?>

<div class="wrapPlugin">
    <?php
    echo $contenidoPaginaPlugin ?? '<p>El plugin no generó ningún contenido para esta página.</p>';
    ?>
</div>

<?php 
?>

<?php
echo partial('layouts/admin-footer', []);
?>