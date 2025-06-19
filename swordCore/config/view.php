<?php

use support\view\NativePhpView;

/**
 * Configuración del motor de vistas.
 *
 * Fichero ajustado para usar plantillas PHP nativas y mantener la estructura de temas.
 */

if (!defined('SWORD_CORE_PATH')) {
    define('SWORD_CORE_PATH', __DIR__);
}
if (!defined('SWORD_CONTENT_PATH')) {
    define('SWORD_CONTENT_PATH', realpath(__DIR__ . '/../swordContent'));
}
if (!defined('SWORD_THEMES_PATH')) {
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . '/themes');
}
if (!defined('SWORD_PLUGINS_PATH')) {
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . '/plugins');
}

// Se obtiene la configuración del tema para determinar el tema activo.
$themeConfig = config('theme', ['active_theme' => 'sword-theme-default']);
$activeTheme = $themeConfig['active_theme'];

// Se construyen las rutas donde se buscarán las vistas, dando prioridad al tema activo.
$viewPaths = [
    // 1. Directorio de vistas del tema activo.
    SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $activeTheme,
    // 2. Directorio de vistas del núcleo (fallback).
    app_path() . DIRECTORY_SEPARATOR . 'view',
];

return [
    'handler' => NativePhpView::class,
    'options' => [
        'cache_path' => runtime_path('views'),
        // Se corrige la clave 'view_path' para que use el array con las rutas del tema y del core.
        // Esto soluciona el problema de que solo se cargaban las vistas del core.
        'view_path' => $viewPaths,
        'namespaces' => [
            'pagination' => base_path() . '/app/view/vendor/pagination',
        ],
    ]
];
