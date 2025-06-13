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
Route::fallback(function(){
    return response('404 not found', 404);
});

// Desactiva la ruta por defecto de Webman para tener control total.
Route::disableDefaultRoute();