<?php

use Webman\Route;

// Importar las rutas de la API
require_once base_path('config/route/api.php');

// Deshabilitar la ruta por defecto si no se necesita
Route::disableDefaultRoute();