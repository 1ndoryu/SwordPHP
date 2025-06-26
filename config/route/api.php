<?php
// ARCHIVO MODIFICADO: config/route/api.php

use Webman\Route;
use app\controller\AuthController;
use app\controller\ContentController;
use app\controller\MediaController;
use app\controller\SystemController;
use app\controller\UserController;
use app\controller\CommentController;
use app\controller\OptionController;
use app\controller\RoleController; // <-- Añadido
use app\middleware\JwtAuthentication;
use app\middleware\RoleMiddleware;

// Ruta de bienvenida para la API
Route::get('/', function () {
    return json([
        'project' => 'Sword v2',
        'status' => 'API is running',
        'version' => '0.9.7' // Versión actualizada
    ]);
});

// Rutas de Sistema (Públicas para entorno de desarrollo/testing)
Route::group('/system', function () {
    Route::post('/install', [SystemController::class, 'install']);
    Route::post('/reset', [SystemController::class, 'reset']);
});

// Rutas de Opciones Globales (Pública para obtenerlas)
Route::get('/options', [OptionController::class, 'index']);

// Rutas de autenticación
Route::group('/auth', function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas de datos del usuario autenticado
Route::group('/user', function () {
    Route::get('/profile', function (support\Request $request) {
        $user = $request->user;
        $user->load('role'); // Asegurarse de que el rol está cargado
        return json([
            'success' => true,
            // Añadir el objeto de rol a la respuesta del perfil
            'user' => $user->only(['id', 'username', 'email', 'role', 'created_at'])
        ]);
    });
    Route::get('/likes', [UserController::class, 'likedContent']);
})->middleware(JwtAuthentication::class);


// --- Rutas de Contenido (CRUD Público y Autenticado) ---
Route::get('/contents', [ContentController::class, 'index']);
Route::get('/contents/{slug}', [ContentController::class, 'show']);

Route::group('/contents', function () {
    Route::post('', [ContentController::class, 'store']);
    Route::post('/{id}', [ContentController::class, 'update']);
    Route::delete('/{id}', [ContentController::class, 'destroy']);
    Route::post('/{id}/like', [ContentController::class, 'toggleLike']);
})->middleware(JwtAuthentication::class);


// --- Rutas de Comentarios (Autenticado) ---
Route::group('/comments', function () {
    Route::post('/{content_id}', [CommentController::class, 'store']);
    Route::delete('/{comment_id}', [CommentController::class, 'destroy']);
})->middleware(JwtAuthentication::class);


// --- Rutas de Media (Autenticado) ---
Route::post('/media', [MediaController::class, 'store'])->middleware(JwtAuthentication::class);


// --- Rutas de Administración (Solo Admin) ---
Route::group('/admin', function () {
    Route::get('/contents', [ContentController::class, 'indexAdmin']);
    Route::get('/media', [MediaController::class, 'index']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);
    
    // Cambiar rol de usuario (POST /admin/users/{id}/role ahora espera 'role_id')
    Route::post('/users/{id}/role', [UserController::class, 'changeRole']);

    // Ruta para actualizar opciones globales
    Route::post('/options', [OptionController::class, 'updateBatch']);

    // --- NUEVO: Grupo para Gestión de Roles ---
    Route::group('/roles', function () {
        Route::get('', [RoleController::class, 'index']);
        Route::post('', [RoleController::class, 'store']);
        Route::post('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

})->middleware([
    JwtAuthentication::class,
    new RoleMiddleware('admin')
]);