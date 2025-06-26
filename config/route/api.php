<?php
// ARCHIVO MODIFICADO: config/route/api.php

use Webman\Route;
use app\controller\AuthController;
use app\controller\ContentController;
use app\controller\MediaController;
use app\controller\SystemController;
use app\controller\UserController;
use app\controller\CommentController;
use app\middleware\JwtAuthentication;
use app\middleware\RoleMiddleware;

// Ruta de bienvenida para la API
Route::get('/', function () {
    return json([
        'project' => 'Sword v2',
        'status' => 'API is running',
        'version' => '0.8.0' // Versión actualizada
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
})->middleware(JwtAuthentication::class);


// --- Rutas de Contenido (CRUD Público y Autenticado) ---
Route::get('/contents', [ContentController::class, 'index']);
Route::get('/contents/{slug}', [ContentController::class, 'show']);

Route::group('/contents', function () {
    Route::post('', [ContentController::class, 'store']);
    Route::post('/{id}', [ContentController::class, 'update']);
    Route::delete('/{id}', [ContentController::class, 'destroy']);
    // --- INICIO DE LA MODIFICACIÓN ---
    // Ruta para dar/quitar like a un contenido
    Route::post('/{id}/like', [ContentController::class, 'toggleLike']);
    // --- FIN DE LA MODIFICACIÓN ---
})->middleware(JwtAuthentication::class);


// --- Rutas de Comentarios (Autenticado) ---
Route::group('/comments', function() {
    // La ruta para crear un comentario está asociada a un contenido específico.
    Route::post('/{content_id}', [CommentController::class, 'store']);
    // La ruta para borrar un comentario es por su ID directo.
    Route::delete('/{comment_id}', [CommentController::class, 'destroy']);
})->middleware(JwtAuthentication::class);


// --- Rutas de Media (Autenticado) ---
// Cualquier usuario autenticado puede subir archivos.
Route::post('/media', [MediaController::class, 'store'])->middleware(JwtAuthentication::class);


// --- Rutas de Administración (Solo Admin) ---
Route::group('/admin', function () {
    // Ver todos los contenidos (incluyendo borradores)
    Route::get('/contents', [ContentController::class, 'indexAdmin']);

    // Rutas de gestión de Media para Admin
    Route::get('/media', [MediaController::class, 'index']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);
    
    // Rutas de gestión de Usuarios para Admin
    Route::post('/users/{id}/role', [UserController::class, 'changeRole']);

    // Rutas de gestión del sistema
    Route::post('/system/install', [SystemController::class, 'install']);
    Route::post('/system/reset', [SystemController::class, 'reset']);
})->middleware([
    JwtAuthentication::class,
    new RoleMiddleware('admin')
]);