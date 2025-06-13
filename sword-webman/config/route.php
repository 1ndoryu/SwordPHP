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
use app\controller\IndexController;

// Define la ruta principal para que cargue la página de inicio.
Route::get('/', [IndexController::class, 'index']);

// Ruta de fallback para deshabilitar rutas por defecto si no se necesitan.
Route::disableDefaultRoute();