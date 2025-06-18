<?php

assetService()->encolarDirectorio('/css/panel', 'css');
assetService()->encolarDirectorio('/js/panel', 'js');

// Obtenemos la información del usuario actual.
$usuario = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina ?? 'Panel de Administración'); ?> | SwordPHP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@200..900&display=swap" rel="stylesheet">
    <?php
    // Imprime las etiquetas <link> de los CSS encolados.
    sw_admin_head();
    ?>
</head>

<body>

    <div class="contenidoPagina">