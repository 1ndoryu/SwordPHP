<?php
/** @var League\Plates\Template\Template $this */
/** @var string $dbVersion */
$this->layout('layout');
?>

<h1>¡Éxito!</h1>
<p>Esta página fue renderizada por una instancia de <code>View</code> que fue inyectada automáticamente por el contenedor en nuestro <code>HomeController</code>.</p>
<p>La vista ahora se carga desde el archivo <code>templates/home.php</code> y utiliza un layout base.</p>

<p><strong>Versión de PostgreSQL:</strong> <?= htmlspecialchars($dbVersion ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></p>