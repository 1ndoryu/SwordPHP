<?php

use Webman\Route;

// Importar las rutas de la API
require_once base_path('config/route/api.php');

// Deshabilitar la ruta por defecto si no se necesita
Route::disableDefaultRoute();

// Fallback para Admin SPA - Ruta raíz
Route::any('/admin', function () {
    return response()->file(public_path() . '/admin/index.html');
});

// Fallback para Admin SPA - Rutas internas (assets, subpáginas manejadas por React Router)
Route::any('/admin/{path:.+}', function ($request, $path) {
    // Si el archivo existe físicamente (ej: assets), dejar que el middleware estático lo sirva.
    // Retornamos false para que Webman continúe con el siguiente middleware/handler (que sería el estático si no se ha ejecutado ya, o 404).
    $file = public_path() . '/admin/' . $path;
    if (is_file($file)) {
        return response()->file($file);
    }
    // Si no es un archivo físico, es una ruta de React Router -> servir index.html
    return response()->file(public_path() . '/admin/index.html');
});
