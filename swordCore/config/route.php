<?php

use Webman\Route;
use App\middleware\AutenticacionMiddleware;
use App\service\OpcionService;
use App\controller\IndexController;
use App\controller\PaginaPublicaController;
use support\Request;
use App\controller\AuthController;
use App\controller\AdminController;
use App\controller\PaginaController;
use App\controller\AjaxController;
use App\controller\TipoContenidoController;
use App\service\TipoContenidoService;
use App\controller\MediaController;
use App\controller\PluginController;
use App\controller\UsuarioController;
use App\controller\TemaController;
use support\Log;

// Ruta principal (raíz del sitio)
Route::get('/', function (Request $request) {
    /** @var OpcionService $opcionService */
    $opcionService = container(OpcionService::class);
    $slugPaginaInicio = $opcionService->obtenerOpcion('pagina_de_inicio_slug');

    // Si se ha configurado una página de inicio estática y tiene un slug, la mostramos.
    if ($slugPaginaInicio) {
        return container(PaginaPublicaController::class)->mostrar($request, $slugPaginaInicio);
    }

    // De lo contrario, mostramos la página de bienvenida por defecto del sistema.
    return container(IndexController::class)->index($request);
});

// Rutas para AJAX y pruebas
Route::post('/ajax', [AjaxController::class, 'handle']);
Route::get('/test-ajax', function () {
    return view('test/ajax');
});

// --- Grupo de Rutas del Panel de Administración ---
$panelGroup = Route::group('/panel', function () {

    // Dashboard principal
    Route::get('', [AdminController::class, 'inicio']);
    Route::get('/', [AdminController::class, 'inicio']);

    // --- CRUD de Páginas ---
    Route::group('/paginas', function () {
        Route::get('', [PaginaController::class, 'index']);
        Route::get('/create', [PaginaController::class, 'create']);
        Route::post('/store', [PaginaController::class, 'store']);
        Route::get('/edit/{id}', [PaginaController::class, 'edit']);
        Route::post('/update/{id}', [PaginaController::class, 'update']);
        Route::post('/destroy/{id}', [PaginaController::class, 'destroy']);
    });

    $tiposDeContenido = TipoContenidoService::getInstancia()->obtenerTodos();
    if (!empty($tiposDeContenido)) {
        $slugs = array_keys($tiposDeContenido);
        // Filtramos 'paginas' por si acaso, aunque ya no debería estar registrado
        $slugs = array_filter($slugs, fn($slug) => $slug !== 'paginas');

        if (!empty($slugs)) {
            $slugRegex = implode('|', $slugs);
            Route::group('/{slug:' . $slugRegex . '}', function () {
                Route::get('', [TipoContenidoController::class, 'index']);
                Route::get('/crear', [TipoContenidoController::class, 'create']);
                Route::post('/crear', [TipoContenidoController::class, 'store']);
                Route::get('/editar/{id:\d+}', [TipoContenidoController::class, 'edit']);
                Route::post('/editar/{id:\d+}', [TipoContenidoController::class, 'update']);
                Route::post('/eliminar/{id:\d+}', [TipoContenidoController::class, 'destroy']);
            });
        }
    }

    // Temas
    Route::get('/temas', [TemaController::class, 'index']);
    Route::post('/temas/activar/{slug}', [TemaController::class, 'activar']);

    // Plugins
    // Plugins
    Route::group('/plugins', function () {
        Route::get('', [PluginController::class, 'index']);
        Route::post('/activar/{slug}', [PluginController::class, 'activar']);
        Route::post('/desactivar/{slug}', [PluginController::class, 'desactivar']);
    });

    // Ajustes Generales
    Route::get('/ajustes', [App\controller\AjustesController::class, 'index']);
    Route::post('/ajustes/guardar', [App\controller\AjustesController::class, 'guardar']);


    // Media
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media/subir', [MediaController::class, 'subir']);

    // Rutas para Usuarios
    Route::group('/usuarios', function () {
        Route::get('', [UsuarioController::class, 'index']);
        Route::get('/crear', [UsuarioController::class, 'create']);
        Route::post('/crear', [UsuarioController::class, 'store']);
        Route::get('/editar/{id:\d+}', [UsuarioController::class, 'edit']);
        Route::post('/update/{id:\d+}', [UsuarioController::class, 'update']);
        Route::post('/eliminar/{id:\d+}', [UsuarioController::class, 'destroy']);
    });
});
// Se aplica el middleware al grupo de rutas del panel.
$panelGroup->middleware([
    AutenticacionMiddleware::class
]);


// --- Rutas de Autenticación (Públicas) ---
Route::get('/registro', [AuthController::class, 'mostrarFormularioRegistro']);
Route::post('/registro', [AuthController::class, 'procesarRegistro']);
Route::get('/login', [AuthController::class, 'mostrarFormularioLogin']);
Route::post('/login', [AuthController::class, 'procesarLogin']);
Route::get('/logout', [AuthController::class, 'procesarLogout']);


// --- Ruteo Dinámico de Páginas del Frontend ---
// CORRECCIÓN: Se elimina la barra (/) de la expresión regular para que no capture rutas de varios niveles como '/panel/ajustes'.
Route::get('/{slug:[a-zA-Z0-9\-_]+}', [PaginaPublicaController::class, 'mostrar']);


// --- Ruta Fallback (Manejo de 404) ---
Route::fallback(function (Request $request) {
    $cabecerasComoString = json_encode($request->header(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    /*$logMessage = sprintf(
        "Ruta no encontrada (404): IP %s intentó acceder a '%s' con User-Agent: %s\nCABECERAS COMPLETAS:\n%s",
        $request->getRealIp(),
        $request->fullUrl(),
        $request->header('user-agent'),
        $cabecerasComoString
    ); */
    // Log::channel('default')->warning($logMessage);
    return response("<h1>404 | No Encontrado</h1><p>La ruta solicitada '{$request->path()}' no fue encontrada en el servidor.</p>", 404);
});

// Desactiva la ruta por defecto de Webman para tener control total.
Route::disableDefaultRoute();
