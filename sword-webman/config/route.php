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
use App\controller\PaginaController;
use App\middleware\AutenticacionMiddleware;
use support\Request;
use support\Log;

// --- Rutas de Administración (Protegidas) ---

// Dashboard Principal
Route::get('/panel', [AdminController::class, 'inicio'])->middleware([
    AutenticacionMiddleware::class
]);

// ========== INICIO: Gestión de Páginas ==========
// Se definen una por una para evitar problemas con Route::group en Windows
// SE HA ELIMINADO ->where('id', '\d+') para compatibilidad
Route::get('/panel/paginas', [PaginaController::class, 'index'])->middleware([
    AutenticacionMiddleware::class
]);
Route::get('/panel/paginas/create', [PaginaController::class, 'create'])->middleware([
    AutenticacionMiddleware::class
]);
Route::post('/panel/paginas/store', [PaginaController::class, 'store'])->middleware([
    AutenticacionMiddleware::class
]);
Route::get('/panel/paginas/edit/{id}', [PaginaController::class, 'edit'])->middleware([ // SIN .where()
    AutenticacionMiddleware::class
]);
Route::post('/panel/paginas/update/{id}', [PaginaController::class, 'update'])->middleware([ // SIN .where()
    AutenticacionMiddleware::class
]);
Route::post('/panel/paginas/destroy/{id}', [PaginaController::class, 'destroy'])->middleware([ // SIN .where()
    AutenticacionMiddleware::class
]);
// ========== FIN: Gestión de Páginas ==========


// --- Rutas de Autenticación ---
Route::get('/registro', [AuthController::class, 'mostrarFormularioRegistro']);
Route::post('/registro', [AuthController::class, 'procesarRegistro']);
Route::get('/login', [AuthController::class, 'mostrarFormularioLogin']);
Route::post('/login', [AuthController::class, 'procesarLogin']);
Route::get('/logout', [AuthController::class, 'procesarLogout']);


// --- Rutas públicas existentes ---
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