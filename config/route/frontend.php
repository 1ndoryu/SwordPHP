<?php
/* 
 * Rutas del Frontend Público
 * 
 * Estas rutas manejan el sitio público (no el admin).
 * Deben cargarse después de las rutas de admin y API
 * para evitar conflictos con rutas más específicas.
 */

use Webman\Route;
use app\controller\FrontendController;

/* 
 * IMPORTANTE: Estas rutas son catch-all y deben estar al final.
 * El orden de carga de rutas importa.
 */

/* Página de inicio */

Route::get('/', [FrontendController::class, 'inicio']);

/* Blog / Archivo de posts */
Route::get('/blog', [FrontendController::class, 'blog']);

/* Contenido individual por slug */
/* Esta ruta debe ser la última por ser la más genérica */
Route::get('/{slug:[a-zA-Z0-9\-_]+}', [FrontendController::class, 'mostrar']);
