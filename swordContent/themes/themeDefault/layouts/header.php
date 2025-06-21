<?php

/**
 * CABECERA GLOBAL DEL SITIO
 *
 * Se espera que la página que lo incluya defina una variable $titulo.
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo ?? 'SwordPHP'); ?></title>
    <p>head de tema</p>
    <?php
    sw_head()
    ?>
    
</head>

<body>

    <?php // Aquí comienza el contenido principal 
    ?>