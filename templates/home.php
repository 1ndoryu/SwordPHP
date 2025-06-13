<?php

/** @var League\Plates\Template\Template $this */
/** @var string $dbVersion */

$this->layout('layout', ['titulo' => 'Página de Inicio']);
?>
<h1>Bienvenido a SwordPHP</h1>
<p>Un micro-framework para construir aplicaciones web rápidas y eficientes.</p>
<p>Versión de la base de datos: <strong><?= htmlspecialchars($dbVersion) ?></strong></p>