<?php

/**
 * Layout principal del panel de administración.
 * Este archivo carga la aplicación React.
 * 
 * Variables disponibles: $title, $user, $content
 */

use app\services\Vite;
use app\services\PostTypeRegistry;

/* Obtener post types para el sidebar */

$postTypes = PostTypeRegistry::all();
$menuItems = [];
foreach ($postTypes as $type) {
    $menuItems[$type['slug']] = [
        'slug' => $type['slug'],
        'nombre' => $type['labels']['menu_name'] ?? ($type['labels']['name'] ?? ucfirst($type['slug'])),
        'icon' => $type['icon'] ?? 'file-text'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> - SwordPHP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/static-admin/css/variables.css">
    <link rel="stylesheet" href="/static-admin/css/init.css">
    <link rel="stylesheet" href="/static-admin/css/style.css">
</head>

<body>
    <div id="root"></div>




    <script>
        /* Datos del servidor para React */
        window.sword = {
            user: <?= json_encode($user ?? 'Admin') ?>,
            postTypes: <?= json_encode($menuItems) ?>,
            baseUrl: '/admin',
            apiUrl: '/api'
        };
    </script>

    <?= Vite::assets('main.tsx') ?>
</body>

</html>