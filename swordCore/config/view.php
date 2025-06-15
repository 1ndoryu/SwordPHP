<?php

use support\view\NativePhpView; // <-- Cambiamos la clase importada

/**
 * Configuración del motor de vistas.
 *
 * Fichero ajustado para usar plantillas PHP nativas
 * y mantener la estructura de temas.
 */

// Se mantiene la lógica para obtener la ruta del tema activo.
$themeConfig = config('theme', ['active_theme' => 'sword-theme-default']);
$activeTheme = $themeConfig['active_theme'];

// Se mantiene la definición de las rutas de las vistas, priorizando el tema.
// La clase NativePhpView usará estas mismas rutas para encontrar los archivos .php
$viewPaths = [
    // Busca primero en el directorio del tema activo.
    str_replace(['/', '\\'], DIRECTORY_SEPARATOR, SWORD_THEMES_PATH . '/' . $activeTheme),
    // Luego busca en el directorio de vistas del core.
    str_replace(['/', '\\'], DIRECTORY_SEPARATOR, app_path() . '/view'),
];

return [
    // El manejador de vistas por defecto. Actualmente se usa PHP nativo.
    'handler' => NativePhpView::class,

    'options' => [
        // Directorio de caché, usado por el caché de esquema de BD y por Blade si se activa.
        'cache_path' => runtime_path('views'),

        // Rutas donde se buscarán las vistas. Necesario para el paginador.
        'view_path' => [
            base_path() . '/app/view'
        ],

        // 'Namespaces' permiten agrupar vistas. El paginador de Laravel usa esto
        // para encontrar sus plantillas (ej. 'pagination::bootstrap-5').
        'namespaces' => [
            'pagination' => base_path() . '/app/view/vendor/pagination',
        ],
    ]
];