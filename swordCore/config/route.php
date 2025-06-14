<?php

use Webman\Route;
use App\controller\IndexController;
use App\controller\AuthController;
use App\controller\AdminController;
use App\controller\PaginaController;
use App\middleware\AutenticacionMiddleware;
use App\controller\AjaxController;
use support\Request;
use support\Log;

// --- Rutas Públicas y de Propósito General ---

// Rutas para AJAX y pruebas
Route::post('/ajax', [AjaxController::class, 'handle']);
Route::get('/test-ajax', function() {
    return view('test/ajax');
});

// --- Grupo de Rutas del Panel de Administración ---
// Todas las rutas dentro de este grupo tendrán el prefijo "/panel"
// y estarán protegidas por el AutenticacionMiddleware.

Route::group('/panel', function () {

    // Dashboard principal: Accede a través de GET /panel o /panel/
    Route::get('', [AdminController::class, 'inicio']);
    Route::get('/', [AdminController::class, 'inicio']);

    // --- CRUD de Páginas ---
    // La ruta base es /paginas, que se convierte en /panel/paginas
    Route::group('/paginas', function () {
        Route::get('', [PaginaController::class, 'index']);          // GET /panel/paginas
        Route::get('/create', [PaginaController::class, 'create']);    // GET /panel/paginas/create
        Route::post('/store', [PaginaController::class, 'store']);     // POST /panel/paginas/store
        Route::get('/edit/{id}', [PaginaController::class, 'edit']);     // GET /panel/paginas/edit/{id}
        Route::post('/update/{id}', [PaginaController::class, 'update']);  // POST /panel/paginas/update/{id}
        Route::post('/destroy/{id}', [PaginaController::class, 'destroy']);// POST /panel/paginas/destroy/{id}
    });

})->middleware([
    AutenticacionMiddleware::class
]);


// --- Rutas de Autenticación (Públicas) ---
Route::get('/registro', [AuthController::class, 'mostrarFormularioRegistro']);
Route::post('/registro', [AuthController::class, 'procesarRegistro']);
Route::get('/login', [AuthController::class, 'mostrarFormularioLogin']);
Route::post('/login', [AuthController::class, 'procesarLogin']);
Route::get('/logout', [AuthController::class, 'procesarLogout']);


// --- Otras Rutas Públicas ---
Route::get('/', [IndexController::class, 'index']);
Route::get('/view', [IndexController::class, 'view']);
Route::get('/json', [IndexController::class, 'json']);
Route::any('/test', [IndexController::class, 'test']);


// --- Ruta Fallback (Manejo de 404) ---
Route::fallback(function(Request $request){
    $cabecerasComoString = json_encode($request->header(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $logMessage = sprintf(
        "Ruta no encontrada (404): IP %s intentó acceder a '%s' con User-Agent: %s\nCABECERAS COMPLETAS:\n%s",
        $request->getRealIp(),
        $request->fullUrl(),
        $request->header('user-agent'),
        $cabecerasComoString
    );
    Log::channel('default')->warning($logMessage);
    return response("<h1>404 | No Encontrado</h1><p>La ruta solicitada '{$request->path()}' no fue encontrada en el servidor.</p>", 404);
});

// Desactiva la ruta por defecto de Webman para tener control total.
Route::disableDefaultRoute();