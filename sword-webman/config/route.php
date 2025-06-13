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
Route::group('/admin', function () {
    // Dashboard principal del admin, accesible en /admin
    Route::get('/', [AdminController::class, 'inicio']);

})->middleware([
    AutenticacionMiddleware::class
]);


// --- Rutas de Autenticación ---

Route::get('/registro', [AuthController::class, 'mostrarFormularioRegistro']);

Route::post('/registro', [AuthController::class, 'procesarRegistro']);

Route::get('/login', [AuthController::class, 'mostrarFormularioLogin']);

Route::post('/login', [AuthController::class, 'procesarLogin']);


// --- Rutas existentes ---
Route::get('/', [IndexController::class, 'index']);

Route::get('/view', [IndexController::class, 'view']);

Route::get('/json', [IndexController::class, 'json']);

Route::any('/test', [IndexController::class, 'test']);


// --- Ruta Fallback (404) ---
Route::fallback(function(Request $request){
    $logMessage = sprintf(
        "Ruta no encontrada (404): IP %s intentó acceder a '%s' con User-Agent: %s",
        $request->getRealIp(),
        $request->fullUrl(),
        $request->header('user-agent')
    );
    Log::channel('default')->warning($logMessage);

    return response("<h1>404 | No Encontrado</h1><p>La ruta solicitada '{$request->path()}' no fue encontrada en el servidor.</p>", 404);
});

// Desactiva la ruta por defecto de Webman para tener control total.
Route::disableDefaultRoute();