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
// El objetivo es definir una estructura de rutas clara y centralizada para
// facilitar la separación entre el núcleo (Core) y el contenido (Content).

// `base_path()` apunta al directorio 'swordCore'.
// `PROJECT_ROOT` será el directorio padre que contendrá a 'swordCore' y 'swordContent'.
define('PROJECT_ROOT', realpath(base_path() . '/..'));

// Ruta al núcleo de la aplicación.
define('SWORD_CORE_PATH', base_path());

// Ruta a la carpeta de contenido del usuario.
define('SWORD_CONTENT_PATH', PROJECT_ROOT . '/swordContent');

// Rutas específicas dentro de 'swordContent'.
define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . '/themes');
define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . '/plugins');
define('SWORD_UPLOADS_PATH', SWORD_CONTENT_PATH . '/uploads');
// ==================== FIN: DEFINICIÓN DE RUTAS DEL PROYECTO =====================

Config::clear();
support\App::loadAllConfig(['route']);
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

$directory = base_path() . '/plugin';
$paths = [config_path()];
foreach (Util::scanDir($directory) as $path) {
    if (is_dir($path = "$path/config")) {
        $paths[] = $path;
    }
}
Route::load($paths);