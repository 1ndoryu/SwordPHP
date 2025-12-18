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
    <?php $postTypes = \app\services\PostTypeRegistry::paraMenu(); ?>
    <div class="layoutAdministracion">
        <div class="bloque admin">
            <aside class="barraLateral">
                <nav>
                    <a href="/admin" class="enlaceNavegacion <?= ($currentRoute ?? '') === 'dashboard' ? 'activo' : '' ?>">Dashboard</a>

                    <div class="seccionMenu">
                        <span class="tituloSeccion">Contenidos</span>
                        <?php foreach ($postTypes as $slug => $tipo): ?>
                            <a href="/admin/<?= $slug ?>" class="enlaceNavegacion <?= ($currentPostType ?? '') === $slug ? 'activo' : '' ?>">
                                <?= $tipo['nombre'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="seccionMenu">
                        <span class="tituloSeccion">Sistema</span>
                        <a href="/admin/media" class="enlaceNavegacion <?= ($currentRoute ?? '') === 'media' ? 'activo' : '' ?>">Medios</a>
                        <a href="/admin/users" class="enlaceNavegacion <?= ($currentRoute ?? '') === 'users' ? 'activo' : '' ?>">Usuarios</a>
                        <a href="/admin/settings" class="enlaceNavegacion <?= ($currentRoute ?? '') === 'settings' ? 'activo' : '' ?>">Ajustes</a>
                    </div>
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
    <?= \app\services\AssetManager::js('admin/js/selectorMedios.js') ?>
</body>

</html>