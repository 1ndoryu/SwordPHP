<?php

/**
 * CABECERA PARA EL PANEL DE ADMINISTRACIÓN
 *
 * Este archivo contiene el inicio del HTML, los assets, la barra lateral
 * y la cabecera del contenido principal.
 *
 * Se espera que la página que lo incluya defina una variable $tituloPagina.
 */

// Encolamos los assets necesarios para el panel.
// Esto equivale a las secciones @estilos y @scripts del layout de Blade.
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

    <?php
    // Imprime las etiquetas <link> de los CSS encolados.
    assetService()->imprimirAssetsHead();
    ?>
</head>

<body>

    <div class="panelContenedor">
        <aside class="panelSidebar">
            <div class="panelSidebarHeader">
                SwordPHP
            </div>
            <nav class="panelSidebarNav">
                <ul>
                    <li><a href="/panel" class="<?php echo request()->path() == 'panel' ? 'activo' : ''; ?>">Dashboard</a></li>
                    <li><a href="/panel/paginas" class="<?php echo str_starts_with(request()->path(), 'panel/paginas') ? 'activo' : ''; ?>">Páginas</a></li>
                    <li><a href="/panel/usuarios" class="<?php echo str_starts_with(request()->path(), 'panel/usuarios') ? 'activo' : ''; ?>">Usuarios</a></li>
                    <li><a href="/panel/ajustes" class="<?php echo request()->path() == 'panel/ajustes' ? 'activo' : ''; ?>">Ajustes</a></li>
                </ul>
            </nav>
        </aside>

        <main class="panelContenidoPrincipal">
            <header class="panelContenidoCabecera">
                <h1><?php echo htmlspecialchars($tituloPagina ?? 'Dashboard'); ?></h1>

                <?php if ($usuario): ?>
                    <div class="infoUsuario">
                        <span>Hola, <?php echo htmlspecialchars($usuario->nombremostrado ?: $usuario->nombreusuario); ?></span>
                        <a href="/logout" class="logoutBtn">Cerrar Sesión</a>
                    </div>
                <?php endif; ?>
            </header>

            <div class="contenidoPagina">