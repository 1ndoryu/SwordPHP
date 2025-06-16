<?php
// 1. Define el título específico para esta página.
// Esto reemplaza a @section('tituloPagina', 'Dashboard')
$tituloPagina = 'Dashboard';

// 2. Incluye la cabecera común del panel de administración.
// Esto reemplaza a @extends('layouts.admin')
echo partial('layouts/admin-header', []);
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div style="background-color: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h3>Sword</h3>
    <p>
        Pagina del panel.
    </p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página común del panel para cerrar la página.
echo partial('layouts/admin-footer', []);
?>