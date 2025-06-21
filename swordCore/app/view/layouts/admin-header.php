<?php

/**
 * CABECERA PARA EL PANEL DE ADMINISTRACIÓN
 *
 * Este archivo contiene el inicio del HTML, los assets, la barra lateral
 * y la cabecera del contenido principal.
 *
 * Se espera que la página que lo incluya defina una variable $tituloPagina.
 */


if (env('CMS_ENABLED', true)) {
    $usuario = currentUser();
}

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

    <div class="panelContenedor">
        <aside class="panelSidebar">
            <div class="panelSidebarHeader iconoB iconoLogo">
                <?php echo icon('logosword') ?>
            </div>
            <nav class="panelSidebarNav">
                <?php echo renderizarMenuLateralAdmin() ?>
            </nav>
        </aside>

        <main class="contenidoPanelP">
            <header class="contenidoPanelC">
                <h1><?php echo htmlspecialchars($tituloPagina ?? 'Dashboard'); ?></h1>

                <?php if ($usuario): ?>
                    <div class="infoUsuario">
                        <span>Hola, <?php echo htmlspecialchars($usuario->nombremostrado ?: $usuario->nombreusuario); ?></span>
                        <a href="#" onclick="if(confirm('¿Estás seguro de que quieres reiniciar el servidor?')) { window.location.href = '/reiniciar-servidor'; }" class="logoutBtn">Reiniciar Servidor</a>
                        <a href="/logout" class="logoutBtn">Cerrar Sesión</a>
                    </div>
                <?php endif; ?>
            </header>

            <div class="contenidoPagina">