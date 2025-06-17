<?php

use Dotenv\Dotenv;
use support\Log;
use Webman\Bootstrap;
use Webman\Config;
use Webman\Middleware;
use Webman\Route;
use Webman\Util;
use Workerman\Events\Select;
use Workerman\Worker;

$worker = $worker ?? null;

if (empty(Worker::$eventLoopClass)) {
    Worker::$eventLoopClass = Select::class;
}

set_error_handler(function ($level, $message, $file = '', $line = 0) {
    if (error_reporting() & $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
});

if ($worker) {
    register_shutdown_function(function ($startTime) {
        if (time() - $startTime <= 0.1) {
            sleep(1);
        }
    }, time());
}

if (class_exists('Dotenv\Dotenv') && file_exists(base_path(false) . '/.env')) {
    if (method_exists('Dotenv\Dotenv', 'createUnsafeMutable')) {
        Dotenv::createUnsafeMutable(base_path(false))->load();
    } else {
        Dotenv::createMutable(base_path(false))->load();
    }
}

// =================== INICIO: DEFINICIÓN DE RUTAS DEL PROYECTO ===================
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', realpath(base_path() . '/..'));
    define('SWORD_CORE_PATH', base_path());
    define('SWORD_CONTENT_PATH', PROJECT_ROOT . '/swordContent');
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . '/themes');
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . '/plugins');
    define('SWORD_UPLOADS_PATH', SWORD_CONTENT_PATH . '/uploads');
}
// ==================== FIN: DEFINICIÓN DE RUTAS DEL PROYECTO =====================

Config::clear();

// Carga de todas las configuraciones EXCEPTO route.php
support\App::loadAllConfig(['route']);

// =================== INICIO: INICIALIZACIÓN DE BOOTSTRAPS DEL CORE Y PLUGINS ===================
// Este bloque se ejecuta ahora para asegurar que los servicios base (como la BD) estén listos
// antes de que los temas o plugins intenten usarlos.
foreach (config('bootstrap', []) as $className) {
    if (!class_exists($className)) {
        $log = "Warning: Class $className setting in config/bootstrap.php not found\r\n";
        echo $log;
        Log::error($log);
        continue;
    }
    /** @var Bootstrap $className */
    $className::start($worker);
}

foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project)) {
            continue;
        }
        foreach ($project['bootstrap'] ?? [] as $className) {
            if (!class_exists($className)) {
                $log = "Warning: Class $className setting in config/plugin/$firm/$name/bootstrap.php not found\r\n";
                echo $log;
                Log::error($log);
                continue;
            }
            /** @var Bootstrap $className */
            $className::start($worker);
        }
    }
    foreach ($projects['bootstrap'] ?? [] as $className) {
        /** @var string $className */
        if (!class_exists($className)) {
            $log = "Warning: Class $className setting in plugin/$firm/config/bootstrap.php not found\r\n";
            echo $log;
            Log::error($log);
            continue;
        }
        /** @var Bootstrap $className */
        $className::start($worker);
    }
}
// ==================== FIN: INICIALIZACIÓN DE BOOTSTRAPS =====================


// ==================== INICIO: CARGA DE FUNCTIONS.PHP DEL TEMA ====================
$themeConfig = config('theme', ['active_theme' => 'sword-theme-default']);
$activeTheme = $themeConfig['active_theme'];
$themeFunctionsFile = SWORD_THEMES_PATH . '/' . $activeTheme . '/functions.php';

if (file_exists($themeFunctionsFile)) {
    require_once $themeFunctionsFile;
}
// ===================== FIN: CARGA DE FUNCTIONS.PHP DEL TEMA ======================

// ===================== INICIO: CARGA DE PLUGINS ACTIVOS ======================
try {
    $opcionService = new \App\service\OpcionService();
    $activePlugins = $opcionService->obtenerOpcion('active_plugins', []);

    if (is_array($activePlugins)) {
        foreach ($activePlugins as $pluginSlug) {
            $pluginFile = SWORD_PLUGINS_PATH . DIRECTORY_SEPARATOR . $pluginSlug . DIRECTORY_SEPARATOR . $pluginSlug . '.php';
            if (is_file($pluginFile)) {
                require_once $pluginFile;
            } else {
                Log::warning("El archivo principal del plugin activo '{$pluginSlug}' no se encontró en: {$pluginFile}");
            }
        }
    }
} catch (\Throwable $e) {
    Log::error("Ocurrió un error crítico durante la carga de plugins: " . $e->getMessage());
}
// ====================== FIN: CARGA DE PLUGINS ACTIVOS =======================

if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}

foreach (config('autoload.files', []) as $file) {
    include_once $file;
}
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project)) {
            continue;
        }
        foreach ($project['autoload']['files'] ?? [] as $file) {
            include_once $file;
        }
    }
    foreach ($projects['autoload']['files'] ?? [] as $file) {
        include_once $file;
    }
}

Middleware::load(config('middleware', []));
foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project) || $name === 'static') {
            continue;
        }
        Middleware::load($project['middleware'] ?? []);
    }
    Middleware::load($projects['middleware'] ?? [], $firm);
    if ($staticMiddlewares = config("plugin.$firm.static.middleware")) {
        Middleware::load(['__static__' => $staticMiddlewares], $firm);
    }
}
Middleware::load(['__static__' => config('static.middleware', [])]);

$directory = base_path() . '/plugin';
$paths = [config_path()];
foreach (Util::scanDir($directory) as $path) {
    if (is_dir($path = "$path/config")) {
        $paths[] = $path;
    }
}
Route::load($paths);
