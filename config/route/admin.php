<?php

use Webman\Route;
use app\controller\Admin\DashboardController;
use app\controller\Admin\AuthController;
use app\controller\Admin\ContentController;
use app\controller\Admin\MediaController;
use app\controller\Admin\ThemeController;

use app\middleware\AdminAuth;

Route::group('/admin', function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'login']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);

    // Protected Routes
    Route::group(function () {
        Route::get('', [DashboardController::class, 'index']);
        Route::get('/', [DashboardController::class, 'index']);

        // Rutas de Contenidos (legacy/fallback - todos los tipos)
        Route::get('/contents', [ContentController::class, 'index']);
        Route::get('/contents/create', [ContentController::class, 'create']);
        Route::post('/contents', [ContentController::class, 'store']);
        Route::get('/contents/{id:\d+}/edit', [ContentController::class, 'edit']);
        Route::put('/contents/{id:\d+}', [ContentController::class, 'update']);
        Route::post('/contents/{id:\d+}', [ContentController::class, 'update']);
        Route::delete('/contents/{id:\d+}', [ContentController::class, 'destroy']);
        Route::get('/contents/trash', [ContentController::class, 'trash']);
        Route::post('/contents/{id:\d+}/restore', [ContentController::class, 'restore']);
        Route::delete('/contents/{id:\d+}/force', [ContentController::class, 'forceDestroy']);
        Route::post('/contents/trash/empty', [ContentController::class, 'emptyTrash']);

        // Rutas de Medios (antes de las rutas dinamicas)
        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media/upload', [MediaController::class, 'upload']);
        Route::get('/media/selector', [MediaController::class, 'selector']);
        Route::get('/media/{id:\d+}', [MediaController::class, 'show']);
        Route::post('/media/{id:\d+}', [MediaController::class, 'update']);
        Route::delete('/media/{id:\d+}', [MediaController::class, 'destroy']);

        // Rutas de Temas
        Route::get('/themes', [ThemeController::class, 'index']);
        Route::get('/themes/active', [ThemeController::class, 'active']);
        Route::get('/themes/{slug:[a-z\-_]+}', [ThemeController::class, 'show']);
        Route::post('/themes/{slug:[a-z\-_]+}/activate', [ThemeController::class, 'activate']);

        // Rutas dinamicas para cualquier Post Type (comodin)
        // El controlador valida si el tipo existe
        Route::get('/{type:[a-z_]+}', [ContentController::class, 'index']);
        Route::get('/{type:[a-z_]+}/create', [ContentController::class, 'create']);
        Route::post('/{type:[a-z_]+}', [ContentController::class, 'store']);
        Route::get('/{type:[a-z_]+}/{id:\d+}/edit', [ContentController::class, 'edit']);
        Route::put('/{type:[a-z_]+}/{id:\d+}', [ContentController::class, 'update']);
        Route::post('/{type:[a-z_]+}/{id:\d+}', [ContentController::class, 'update']);
        Route::delete('/{type:[a-z_]+}/{id:\d+}', [ContentController::class, 'destroy']);
        Route::get('/{type:[a-z_]+}/trash', [ContentController::class, 'trash']);
        Route::post('/{type:[a-z_]+}/{id:\d+}/restore', [ContentController::class, 'restore']);
        Route::delete('/{type:[a-z_]+}/{id:\d+}/force', [ContentController::class, 'forceDestroy']);
        Route::post('/{type:[a-z_]+}/trash/empty', [ContentController::class, 'emptyTrash']);
    })->middleware([AdminAuth::class]);
});
