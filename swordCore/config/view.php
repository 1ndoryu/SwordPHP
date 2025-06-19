<?php

use support\view\NativePhpView;

/**
 * Configuración del motor de vistas.
 *
 * Fichero ajustado para usar plantillas PHP nativas y mantener la estructura de temas.
 */

// ¡SOLUCIÓN DEFINITIVA!
// Se usa dirname(base_path()) para obtener la ruta raíz del proyecto (la carpeta que contiene a swordCore)
// y construir las rutas de forma segura y agnóstica al sistema operativo.
$projectRoot = dirname(base_path());

// Se definen las constantes de ruta globalmente para que el resto del sistema pueda usarlas.
// El if() previene errores si las constantes ya fueron definidas en start.php
if (!defined('SWORD_CONTENT_PATH')) {
    define('SWORD_CONTENT_PATH', $projectRoot . DIRECTORY_SEPARATOR . 'swordContent');
}
if (!defined('SWORD_THEMES_PATH')) {
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'themes');
}
if (!defined('SWORD_PLUGINS_PATH')) {
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'plugins');
}

/**
 * Configuración inicial del motor de vistas.
 *
 * El manejador ('handler') NativePhpView ahora es responsable de determinar
 * dinámicamente las rutas de las vistas al momento de renderizar,
 * cargando el tema activo desde la base de datos a través del TemaService.
 */
return [
    'handler' => NativePhpView::class,
    'options' => [
        'cache_path' => runtime_path('views'),
        // La clave 'view_path' ya no es necesaria aquí. El manejador de vistas la gestiona internamente.
        'namespaces' => [
            'pagination' => base_path() . '/app/view/vendor/pagination',
        ],
    ]
];
