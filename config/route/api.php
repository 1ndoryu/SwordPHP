<?php

use Webman\Route;
use app\controller\AuthController;

// Ruta de bienvenida para la API
Route::get('/', function () {
    return json([
        'project' => 'Sword v2',
        'status' => 'API is running',
        'version' => '0.2.0' // Version updated
    ]);
});

// Rutas de autenticación
Route::group('/auth', function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Ejemplo de una ruta protegida
Route::get('/user/profile', function (support\Request $request) {
    return json([
        'success' => true,
        'user' => $request->user->only(['id', 'username', 'email', 'role', 'created_at'])
    ]);
})->middleware([
    'auth' // Usamos el alias del middleware que registramos
]);