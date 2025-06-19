<?php

use support\view\NativePhpView;
use support\Config;

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

// === INICIO DE LA MODIFICACIÓN: Cargar tema desde la Base de Datos ===

// 1. Definir el tema por defecto desde el archivo de configuración como fallback.
$fallbackTheme = (require __DIR__ . '/theme.php')['active_theme'] ?? 'sword-theme-default';
$activeTheme = $fallbackTheme;

try {
    // Las variables de entorno (.env) ya han sido cargadas por support/bootstrap.php.
    // Verificamos que existan las variables mínimas para intentar la conexión.
    if (isset($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'])) {
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $_ENV['DB_CONNECTION'] ?? 'mysql',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'] ?? 3306,
            $_ENV['DB_DATABASE'],
            'utf8mb4'
        );

        // Usar una conexión PDO simple para no depender del ciclo de vida de Eloquent en este punto.
        $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2 // Timeout corto para no ralentizar el inicio en caso de fallo.
        ]);

        $stmt = $pdo->query("SELECT opcion_valor FROM opciones WHERE opcion_nombre = 'active_theme' LIMIT 1");
        $dbTheme = $stmt->fetchColumn();

        if ($dbTheme && is_string($dbTheme) && !empty($dbTheme)) {
            $activeTheme = $dbTheme;
        }
    }
} catch (\Throwable $e) {
    // Si hay cualquier error (ej. en la instalación, la tabla no existe o la BD no responde),
    // simplemente usamos el tema del archivo de configuración. No se detiene la ejecución.
}

// 2. Sobrescribir la configuración en memoria para que toda la aplicación
// utilice el tema correcto (sea el de la BD o el de fallback).
Config::set('theme.active_theme', $activeTheme);

// === FIN DE LA MODIFICACIÓN ===


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
