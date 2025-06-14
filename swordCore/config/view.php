<?php
use support\view\Blade;

/**
 * Configuración del motor de vistas.
 *
 * Fichero corregido para soportar temas y mantener la configuración
 * de 'namespaces' para la paginación.
 */

// Cargar la configuración del tema para obtener el tema activo.
$themeConfig = config('theme', ['active_theme' => 'sword-theme-default']);
$activeTheme = $themeConfig['active_theme'];

// Definir las rutas para las vistas, priorizando el tema.
$viewPaths = [
    SWORD_THEMES_PATH . '/' . $activeTheme,
    app_path() . '/view',
];

return [
    'handler' => Blade::class,
    'options' => [
        // Se utiliza el array de rutas para las vistas.
        'view_path' => $viewPaths,

        // Ruta de caché para las vistas compiladas.
        'cache_path' => runtime_path() . '/views',
        
        // Se preserva la configuración de namespaces.
        // Esto es importante para componentes como la paginación.
        'namespaces' => [
            'pagination' => base_path() . '/vendor/illuminate/pagination/resources/views'
        ]
    ]
];