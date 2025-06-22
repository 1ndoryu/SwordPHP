<?php

use Webman\Route;
use support\Request;
use App\controller\InstallerController;
use App\controller\IndexController;
use App\controller\Api\V1\ContentApiController;

// --- Verificación del Instalador (se ejecuta siempre) ---
if (!file_exists(runtime_path('installed.lock'))) {
    Route::get('/install', [InstallerController::class, 'showStep']);
    Route::post('/install', [InstallerController::class, 'processStep']);
    Route::any('/{route:.*}', fn(Request $request) => $request->path() !== '/install' ? redirect('/install') : null);
    return;
}

// --- Bifurcación de rutas basada en la configuración del CMS ---

if (env('CMS_ENABLED', true)) {
    // ===================================
    // MODO CMS COMPLETO (CMS Activado)
    // ===================================

    // Importaciones de clases necesarias para el CMS
    class_alias(\App\middleware\AutenticacionMiddleware::class, 'AutenticacionMiddleware');
    class_alias(\App\service\OpcionService::class, 'OpcionService');
    class_alias(\App\controller\PaginaPublicaController::class, 'PaginaPublicaController');
    class_alias(\App\controller\AuthController::class, 'AuthController');
    class_alias(\App\controller\AdminController::class, 'AdminController');
    class_alias(\App\controller\PaginaController::class, 'PaginaController');
    class_alias(\App\controller\AjaxController::class, 'AjaxController');
    class_alias(\App\controller\TipoContenidoController::class, 'TipoContenidoController');
    class_alias(\App\service\TipoContenidoService::class, 'TipoContenidoService');
    class_alias(\App\controller\MediaController::class, 'MediaController');
    class_alias(\App\controller\PluginController::class, 'PluginController');
    class_alias(\App\controller\UsuarioController::class, 'UsuarioController');
    class_alias(\App\controller\TemaController::class, 'TemaController');
    class_alias(\App\controller\PluginPageController::class, 'PluginPageController');
    class_alias(\App\controller\AjustesController::class, 'AjustesController');
    
    // Ruta principal del CMS
    Route::get('/', function (Request $request) {
        $opcionService = container(OpcionService::class);
        $slugPaginaInicio = $opcionService->getOption('pagina_de_inicio_slug');
        if ($slugPaginaInicio) {
            return container(PaginaPublicaController::class)->mostrar($request, $slugPaginaInicio);
        }
        return container(IndexController::class)->index($request);
    });

    // Rutas para AJAX
    Route::post('/ajax', [AjaxController::class, 'handle']);
    Route::group('/panel/ajax', function () {
        Route::get('/obtener-galeria', [AjaxController::class, 'obtenerGaleria']);
        Route::get('/obtener-media-info/{id}', [AjaxController::class, 'obtenerMediaInfo']);
    })->middleware([\App\middleware\Session::class, AutenticacionMiddleware::class]);

    Route::get('/reiniciar-servidor', [AdminController::class, 'reiniciarServidor']);

    // Grupo de Rutas del Panel de Administración
    $panelGroup = Route::group('/panel', function () {
        Route::get('', [AdminController::class, 'inicio']);
        Route::get('/', [AdminController::class, 'inicio']);

        Route::group('/paginas', function () {
            Route::get('', [PaginaController::class, 'index']);
            Route::get('/create', [PaginaController::class, 'create']);
            Route::post('/store', [PaginaController::class, 'store']);
            Route::get('/edit/{id}', [PaginaController::class, 'edit']);
            Route::post('/update/{id}', [PaginaController::class, 'update']);
            Route::post('/destroy/{id}', [PaginaController::class, 'destroy']);
            Route::post('/restaurar/{id}', [PaginaController::class, 'restaurar']);
        });
        $tiposDeContenido = TipoContenidoService::getInstancia()->obtenerTodos();
        if (!empty($tiposDeContenido)) {
            $slugs = array_keys($tiposDeContenido);
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
                    Route::get('/ajustes', [TipoContenidoController::class, 'mostrarAjustes']);
                    Route::post('/ajustes', [TipoContenidoController::class, 'guardarAjustes']);
                });
            }
        }
        Route::get('/temas', [TemaController::class, 'index']);
        Route::post('/temas/activar/{slug}', [TemaController::class, 'activar']);
        Route::group('/plugins', function () {
            Route::get('', [PluginController::class, 'index']);
            Route::post('/activar/{slug}', [PluginController::class, 'activar']);
            Route::post('/desactivar/{slug}', [PluginController::class, 'desactivar']);
        });
        Route::group('/ajustes', function () {
            Route::get('', [AjustesController::class, 'index']);
            Route::post('/guardar', [AjustesController::class, 'guardar']);
            Route::get('/enlaces-permanentes', [AjustesController::class, 'enlacesPermanentes']);
            Route::post('/enlaces-permanentes', [AjustesController::class, 'guardarEnlacesPermanentes']);
            Route::get('/{slug}', [PluginPageController::class, 'mostrar']);
        });
        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media/subir', [MediaController::class, 'subir']);
        Route::post('/media/destroy/{id}', [MediaController::class, 'destroy']);
        Route::group('/usuarios', function () {
            Route::get('', [UsuarioController::class, 'index']);
            Route::get('/crear', [UsuarioController::class, 'create']);
            Route::post('/crear', [UsuarioController::class, 'store']);
            Route::get('/editar/{id:\d+}', [UsuarioController::class, 'edit']);
            Route::post('/update/{id:\d+}', [UsuarioController::class, 'update']);
            Route::post('/eliminar/{id:\d+}', [UsuarioController::class, 'destroy']);
            Route::post('/generar-token-api/{id:\d+}', [UsuarioController::class, 'generarTokenApi']);
        });
    });
    $panelGroup->middleware([AutenticacionMiddleware::class]);

    // Rutas de Autenticación del Panel
    Route::get('/registro', [AuthController::class, 'mostrarFormularioRegistro']);
    Route::post('/registro', [AuthController::class, 'procesarRegistro']);
    Route::get('/login', [AuthController::class, 'mostrarFormularioLogin']);
    Route::post('/login', [AuthController::class, 'procesarLogin']);
    Route::get('/logout', [AuthController::class, 'procesarLogout']);

    // Ruteo Dinámico de Páginas del Frontend
    $permalinks_file = support_path('permalinks_generated.php');
    if (file_exists($permalinks_file)) {
        require_once $permalinks_file;
    }

    // === INICIO RUTAS API ===

    // --- RUTA PÚBLICA DE AUTENTICACIÓN ---
    // CORRECCIÓN: Se añade el namespace correcto 'V1' al controlador.
    Route::post('/auth/token', [\App\controller\Api\V1\ApiAuthController::class, 'token']);

    // --- RUTAS PROTEGIDAS DE API v1 ---
    Route::group('/api/v1', function () {
        // --- Endpoints de Contenido (Públicos) ---
        Route::get('/content', [ContentApiController::class, 'index']);
        Route::get('/content/{id:\d+}', [ContentApiController::class, 'show']);

        // --- Endpoints Protegidos ---
        Route::group(function () {
            // Contenido (Crear, Actualizar, Eliminar)
            Route::post('/content', [ContentApiController::class, 'store']);
            Route::put('/content/{id:\d+}', [ContentApiController::class, 'update']);
            Route::patch('/content/{id:\d+}', [ContentApiController::class, 'update']);
            Route::delete('/content/{id:\d+}', [ContentApiController::class, 'destroy']);

            // --- Endpoints de Usuarios (CRUD Completo) ---
            Route::get('/users', [\App\controller\Api\V1\UserApiController::class, 'index']);
            Route::post('/users', [\App\controller\Api\V1\UserApiController::class, 'store']);
            Route::get('/users/{id:\d+}', [\App\controller\Api\V1\UserApiController::class, 'show']);
            Route::put('/users/{id:\d+}', [\App\controller\Api\V1\UserApiController::class, 'update']);
            Route::patch('/users/{id:\d+}', [\App\controller\Api\V1\UserApiController::class, 'update']);
            Route::delete('/users/{id:\d+}', [\App\controller\Api\V1\UserApiController::class, 'destroy']);

            // --- Endpoints de Opciones ---
            Route::get('/options/{key:.+}', [\App\controller\Api\V1\OptionApiController::class, 'show']);
            Route::post('/options', [\App\controller\Api\V1\OptionApiController::class, 'store']);

        })->middleware([\App\middleware\ApiAuthMiddleware::class]);
    });

} else {
    Route::get('/', [IndexController::class, 'index']);
}

// --- Ruta Fallback y cierre (siempre activa) ---
Route::fallback(fn(Request $request) => response("<h1>404 | No Encontrado</h1><p>La ruta solicitada '{$request->path()}' no fue encontrada.</p>", 404));
Route::disableDefaultRoute();