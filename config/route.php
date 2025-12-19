<?php

use Webman\Route;

// Importar las rutas de la API
require_once base_path('config/route/api.php');
// Importar rutas de Admin
require_once base_path('config/route/admin.php');

// Importar rutas del Frontend (deben ir al final por ser catch-all)
require_once base_path('config/route/frontend.php');

// Deshabilitar la ruta por defecto si no se necesita
Route::disableDefaultRoute();
