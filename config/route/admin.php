<?php

use Webman\Route;
use app\controller\Admin\DashboardController;
use app\controller\Admin\AuthController;
use app\controller\Admin\ContentController;

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

        // Rutas de Contenidos
        Route::get('/contents', [ContentController::class, 'index']);
        Route::get('/contents/create', [ContentController::class, 'create']);
        Route::post('/contents', [ContentController::class, 'store']);
        Route::get('/contents/{id:\d+}/edit', [ContentController::class, 'edit']);
        Route::put('/contents/{id:\d+}', [ContentController::class, 'update']);
        Route::post('/contents/{id:\d+}', [ContentController::class, 'update']);
        Route::delete('/contents/{id:\d+}', [ContentController::class, 'destroy']);
    })->middleware([AdminAuth::class]);
});
