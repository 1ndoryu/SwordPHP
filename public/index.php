<?php

/**
 * SwordPHP - Punto de Entrada CGI
 * 
 * Este archivo permite que SwordPHP funcione en hosting tradicional
 * (Apache, Nginx) sin necesidad del proceso persistente de Webman/Workerman.
 * 
 * Compatible con:
 * - Shared hosting
 * - cPanel / Plesk
 * - Cualquier servidor web con PHP
 * 
 * @version 2.0.0 - Unificación del sistema de rutas con CgiRouteShim
 */

/* Definir BASE_PATH antes de cargar cualquier cosa */
define('BASE_PATH', dirname(__DIR__));

/* 
 * ESTRATEGIA DE INTERCEPCIÓN DE CLASES:
 * 
 * Definimos aliases de clases ANTES de cargar Composer para que cuando
 * se usen estas clases, PHP use nuestras versiones CGI en lugar de las 
 * versiones de Webman/Workerman.
 * 
 * Clases interceptadas:
 * - Webman\Route → CgiRouteShim (para usar mismos archivos de rutas)
 * - support\Request → CgiRequest (para type hints en controladores)
 */

/* Cargar las dependencias CGI manualmente (sin Composer aún) */
require_once BASE_PATH . '/app/support/CgiSession.php';
require_once BASE_PATH . '/app/support/CgiRequest.php';
require_once BASE_PATH . '/app/support/CgiRouter.php';
require_once BASE_PATH . '/app/support/CgiRouteShim.php';

/* Interceptar Webman\Route → CgiRouteShim */
if (!class_exists('Webman\\Route', false)) {
    class_alias('app\\support\\CgiRouteShim', 'Webman\\Route');
}

/* Interceptar support\Request → CgiRequest */
if (!class_exists('support\\Request', false)) {
    class_alias('app\\support\\CgiRequest', 'support\\Request');
}

/* Cargar autoloader de Composer */
require_once BASE_PATH . '/vendor/autoload.php';

use app\support\Environment;
use app\support\CgiRequest;
use app\support\CgiResponse;
use app\support\CgiRouter;

/* 
 * Verificar si estamos en modo Webman.
 * Si Workerman está activo, este archivo no debería ejecutarse.
 */

if (Environment::esWebman()) {
    die('Este archivo es solo para modo CGI. Use php windows.php o php start.php para modo Webman.');
}

/* Cargar bootstrap CGI */
require_once BASE_PATH . '/app/support/cgi_bootstrap.php';

/* Servir archivos estáticos directamente */
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

if (esArchivoEstatico($path)) {
    /* Primero buscar en public/ */
    $archivoPublico = BASE_PATH . '/public' . $path;

    if (file_exists($archivoPublico) && is_file($archivoPublico)) {
        servirArchivoEstatico($archivoPublico);
        exit;
    }

    /* Si es ruta de temas (/themes/...), buscar en la raíz */
    if (str_starts_with($path, '/themes/')) {
        $archivoTema = BASE_PATH . $path;

        if (file_exists($archivoTema) && is_file($archivoTema)) {
            servirArchivoEstatico($archivoTema);
            exit;
        }
    }
}

/* 
 * UNIFICACIÓN DE RUTAS (v2.0)
 * 
 * Las rutas se cargan directamente desde los archivos de configuración.
 * El autoloader interceptor al inicio de este archivo redirige Webman\Route
 * a CgiRouteShim automáticamente.
 */

/* Cargar las rutas desde los archivos de configuración */
require_once BASE_PATH . '/config/route/admin.php';
require_once BASE_PATH . '/config/route/api.php';
require_once BASE_PATH . '/config/route/frontend.php';

/* Crear request CGI */
$request = new CgiRequest();

/* Despachar la solicitud */
$router = CgiRouter::instancia();
$response = $router->despachar($request);

/* Enviar respuesta */
$response->enviar();

/* ========================================= */
/* Funciones auxiliares                      */
/* ========================================= */

/**
 * Determina si la ruta es un archivo estático.
 */
function esArchivoEstatico(string $path): bool
{
    $extensionesEstaticas = [
        'css',
        'js',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'otf',
        'mp3',
        'mp4',
        'webm',
        'ogg',
        'pdf',
        'zip',
        'json',
        'xml',
        'txt',
        'map',
        'webp'
    ];

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($extension, $extensionesEstaticas);
}

/**
 * Sirve un archivo estático con headers apropiados.
 */
function servirArchivoEstatico(string $path): void
{
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'otf' => 'font/otf',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'pdf' => 'application/pdf',
        'xml' => 'application/xml',
        'txt' => 'text/plain',
        'map' => 'application/json',
    ];

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimeType = $mimeTypes[$extension] ?? mime_content_type($path) ?? 'application/octet-stream';

    /* Headers de caché para archivos estáticos */
    $cacheDuracion = 86400 * 7; /* 7 días */

    header("Content-Type: {$mimeType}");
    header("Content-Length: " . filesize($path));
    header("Cache-Control: public, max-age={$cacheDuracion}");
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + $cacheDuracion) . ' GMT');
    header("Last-Modified: " . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');

    readfile($path);
}
