<?php
// Preparamos las variables específicas para esta vista
$titulo = '¡Inyección de Dependencias!';

// Capturamos el contenido principal de esta vista en un buffer
ob_start();
?>

<h1>¡Éxito!</h1>
<p>Esta página fue renderizada por una instancia de <code>View</code> que fue inyectada automáticamente por el contenedor en nuestro <code>HomeController</code>.</p>
<p>La vista ahora se carga desde el archivo <code>templates/home.php</code> y utiliza un layout base.</p>

<?php
// Guardamos el contenido del buffer en la variable que el layout espera
$contenido = ob_get_clean();

// Incluimos la plantilla de layout principal, que usará las variables $titulo y $contenido
include 'layout.php';