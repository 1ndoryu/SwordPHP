<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - SwordPHP</title>
    <?= \app\services\AssetManager::css('admin/css/variables.css') ?>
    <?= \app\services\AssetManager::css('admin/css/init.css') ?>
    <?= \app\services\AssetManager::css('admin/css/style.css') ?>
</head>

<body>
    <div class="layoutAdministracion">
        <div class="bloque admin">
            <aside class="barraLateral">
                <nav>
                    <a href="/admin" class="enlaceNavegacion activo">Dashboard</a>
                    <a href="/admin/contents" class="enlaceNavegacion">Contenidos</a>
                    <a href="/admin/media" class="enlaceNavegacion">Medios</a>
                    <a href="/admin/users" class="enlaceNavegacion">Usuarios</a>
                    <a href="/admin/settings" class="enlaceNavegacion">Ajustes</a>
                </nav>
            </aside>
            <main class="contenidoPrincipal">
                <header class="encabezado">
                    <h1><?= $title ?? 'Dashboard' ?></h1>
                    <div class="menuUsuario">
                        Hola, <?= $user ?? 'Usuario' ?> | <a href="/admin/logout" data-no-spa>Salir</a>
                    </div>
                </header>
                <div class="contenido" id="contenidoPrincipal">
                    <?php if (isset($content)) echo $content; ?>
                </div>
            </main>
        </div>
    </div>
    <?= \app\services\AssetManager::js('admin/js/tabs.js') ?>
    <?= \app\services\AssetManager::js('admin/js/spa.js') ?>
</body>

</html>