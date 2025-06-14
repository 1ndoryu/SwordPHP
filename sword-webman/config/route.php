<?php
/**
 * This file is part of webman.
 *
 * (c) 2020-2022 CRT. All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Webman\Route;
use App\controller\IndexController;
use App\controller\AuthController;
use App\controller\AdminController;
use App\middleware\AutenticacionMiddleware;
use support\Request;
use support\Log;

// --- Rutas de Administración (Protegidas) ---
// CORRECCIÓN FINAL: Se usa una ruta directa y se le aplica el middleware.

/*
Habia un problema en windows aca 

Route::group('/panel', function () {
    Route::get('/', [AdminController::class, 'inicio']);
})->middleware([ 
    AutenticacionMiddleware::class
]);

un problema complejo y poco común, pero al final logramos identificar que la causa era una incompatibilidad muy específica de la función Route::group en el entorno de ejecución en Windows. La solución final, que consiste en usar una ruta directa (Route::get) con su middleware aplicado directamente, es una forma robusta y limpia de evitar ese inconveniente.
*/

Route::get('/panel', [AdminController::class, 'inicio'])->middleware([
    AutenticacionMiddleware::class
]);


// --- Rutas de Autenticación ---
Route::get('/registro', [AuthController::class, 'mostrarFormularioRegistro']);
Route::post('/registro', [AuthController::class, 'procesarRegistro']);
Route::get('/login', [AuthController::class, 'mostrarFormularioLogin']);
Route::post('/login', [AuthController::class, 'procesarLogin']);
Route::get('/logout', [AuthController::class, 'procesarLogout']);


// --- Rutas existentes ---
Route::get('/', [IndexController::class, 'index']);
Route::get('/view', [IndexController::class, 'view']);
Route::get('/json', [IndexController::class, 'json']);
Route::any('/test', [IndexController::class, 'test']);


// --- Ruta Fallback (404) ---
Route::fallback(function(Request $request){
    $cabecerasComoString = json_encode($request->header(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $logMessage = sprintf(
        "Ruta no encontrada (404): IP %s intentó acceder a '%s' con User-Agent: %s\nCABECERAS COMPLETAS:\n%s",
        $request->getRealIp(),
        $request->fullUrl(),
        $request->header('user-agent'),
        $cabecerasComoString
    );
    Log::channel('default')->warning($logMessage);
    return response("<h1>404 | No Encontrado</h1><p>La ruta solicitada '{$request->path()}' no fue encontrada en el servidor.</p>", 404);
});

// Desactiva la ruta por defecto de Webman para tener control total.
Route::disableDefaultRoute();