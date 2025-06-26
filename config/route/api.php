<?php
// ARCHIVO MODIFICADO: config/route/api.php

use Webman\Route;
use app\controller\AuthController;
use app\controller\ContentController;
use app\controller\MediaController; // <-- Añadido
use app\middleware\JwtAuthentication;
use app\middleware\RoleMiddleware;

// Ruta de bienvenida para la API
Route::get('/', function () {
    return json([
        'project' => 'Sword v2',
        'status' => 'API is running',
        'version' => '0.5.0' // <-- Versión actualizada
    ]);
});

// Rutas de autenticación
Route::group('/auth', function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Ejemplo de una ruta protegida (Perfil de usuario)
Route::get('/user/profile', function (support\Request $request) {
    return json([
        'success' => true,
        'user' => $request->user->only(['id', 'username', 'email', 'role', 'created_at'])
    ]);
})->middleware([
    JwtAuthentication::class
]);


// --- Rutas de Contenido (CRUD) ---
Route::get('/contents', [ContentController::class, 'index']);
Route::get('/contents/{slug}', [ContentController::class, 'show']);

Route::group('/contents', function () {
    Route::post('', [ContentController::class, 'store']);
    Route::post('/{id}', [ContentController::class, 'update']);
    Route::delete('/{id}', [ContentController::class, 'destroy']);
})->middleware([
    JwtAuthentication::class
]);

// --- INICIO DE LA MODIFICACIÓN ---

// --- Rutas de Media ---
// Cualquier usuario autenticado puede subir archivos.
Route::post('/media', [MediaController::class, 'store'])->middleware([
    JwtAuthentication::class
]);

// --- FIN DE LA MODIFICACIÓN ---


// --- Rutas de Administración ---
// Rutas protegidas que requieren rol de 'admin'
Route::group('/admin', function () {
    // Ver todos los contenidos (incluyendo borradores)
    Route::get('/contents', [ContentController::class, 'indexAdmin']);

    // --- INICIO DE LA MODIFICACIÓN ---
    // Rutas de gestión de Media para Admin
    Route::get('/media', [MediaController::class, 'index']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);
    // --- FIN DE LA MODIFICACIÓN ---

})->middleware([
    JwtAuthentication::class,
    RoleMiddleware::class . ':admin'
]);