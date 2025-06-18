<?php
/**
 * Archivo de rutas de enlaces permanentes.
 * Este archivo es generado y sobreescrito automáticamente por los Ajustes de Enlaces Permanentes.
 * NO MODIFICAR MANUALMENTE.
 */

use Webman\Route;
use App\controller\PaginaPublicaController;

// Estructura actual: /%slug%/
Route::get('/{slug:[a-zA-Z0-9\-_]+}', [PaginaPublicaController::class, 'mostrar']);
