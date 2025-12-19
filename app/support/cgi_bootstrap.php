<?php

/**
 * Bootstrap CGI para SwordPHP.
 * Inicializa la aplicación en modo CGI tradicional (Apache/Nginx).
 */

use Dotenv\Dotenv;
use Webman\Config;

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}

/* 
 * NOTA: El autoloader interceptor para Webman\Route está en public/index.php
 * y debe ejecutarse ANTES de cargar Composer para que funcione correctamente.
 */

/* Cargar autoloader de Composer (si no se ha cargado ya) */
require_once BASE_PATH . '/vendor/autoload.php';

/* Cargar variables de entorno */
if (class_exists(Dotenv::class) && file_exists(BASE_PATH . '/.env')) {
    if (method_exists(Dotenv::class, 'createUnsafeMutable')) {
        Dotenv::createUnsafeMutable(BASE_PATH)->load();
    } else {
        Dotenv::createMutable(BASE_PATH)->load();
    }
}

/* 
 * Cargar configuración excluyendo archivos específicos de Webman/Workerman.
 * - route: Se carga manualmente después vía CgiRouteShim
 * - container: Específico de DI de Webman
 * - process: Usa $argv que no existe en modo CGI
 * - server: Configuración del servidor Workerman
 */
Config::load(BASE_PATH . '/config', ['route', 'container', 'process', 'server']);

/* Establecer timezone */
$appConfig = require BASE_PATH . '/config/app.php';
if ($timezone = $appConfig['default_timezone'] ?? null) {
    date_default_timezone_set($timezone);
}

/* Configurar error reporting */
$errorReporting = $appConfig['error_reporting'] ?? E_ALL;
error_reporting($errorReporting);

if ($appConfig['debug'] ?? false) {
    ini_set('display_errors', 'on');
} else {
    ini_set('display_errors', 'off');
}

/* Cargar archivos de autoload */
$autoloadFiles = config('autoload.files', []);
foreach ($autoloadFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

/* Crear directorios de runtime si no existen */
$runtimeDirs = [
    BASE_PATH . '/runtime',
    BASE_PATH . '/runtime/logs',
    BASE_PATH . '/runtime/views',
    BASE_PATH . '/runtime/sessions',
];

foreach ($runtimeDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/* Configurar sesiones nativas de PHP */
$sessionConfig = config('session', []);
$sessionPath = BASE_PATH . '/runtime/sessions';

ini_set('session.save_path', $sessionPath);
ini_set('session.gc_maxlifetime', $sessionConfig['lifetime'] ?? 1440);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.use_strict_mode', '1');

/* Inicializar base de datos (Eloquent) */
\app\bootstrap\Database::start(null);

/* Cargar helpers CGI adicionales */
require_once __DIR__ . '/cgi_helpers.php';
