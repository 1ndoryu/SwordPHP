<?php

use Webman\Route;
use app\controller\Admin\DashboardController;
use app\controller\Admin\AuthController;

Route::group('/admin', function () {
    // Dashboard
    Route::get('', [DashboardController::class, 'index']);
    Route::get('/', [DashboardController::class, 'index']);

    // Auth
    Route::get('/login', [AuthController::class, 'login']);
});
