<?php
// config/route/api.php

use Webman\Route;
use app\controller\AuthController;
use app\controller\ContentController;
use app\controller\FeedController;
use app\controller\MediaController;
use app\controller\SystemController;
use app\controller\UserController;
use app\controller\CommentController;
use app\controller\OptionController;
use app\controller\RoleController;
use app\controller\WebhookController;
use app\middleware\JwtAuthentication;
use app\middleware\PermissionMiddleware;

// Ruta de bienvenida para la API
Route::get('/', function () {
    return json([
        'project' => 'Sword v2',
        'status' => 'API is running',
        'version' => '1.0.0'
    ]);
});

// Rutas de Sistema (Públicas para entorno de desarrollo/testing)
Route::group('/system', function () {
    Route::post('/install', [SystemController::class, 'install']);
    Route::post('/reset', [SystemController::class, 'reset']);
});

// Webhook público desde Casiel
Route::post('/webhooks/casiel/processed', [WebhookController::class, 'handleCasielProcessed']);

// Rutas de Opciones Globales (Pública para obtenerlas)
Route::get('/options', [OptionController::class, 'index']);

// Rutas de autenticación
Route::group('/auth', function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas de datos del usuario autenticado
Route::group('/user', function () {
    Route::get('/profile', function (support\Request $request) {
        $user = $request->user;
        $user->load('role'); // Asegurarse de que el rol está cargado
        return json([
            'success' => true,
            'user' => $user->only(['id', 'username', 'email', 'role', 'created_at', 'profile_data'])
        ]);
    });
    Route::post('/profile', [UserController::class, 'updateProfile']);
    Route::get('/likes', [UserController::class, 'likedContent']);
})->middleware(JwtAuthentication::class);

// --- INICIO: NUEVAS RUTAS DE FOLLOW (protegidas) ---
Route::post('/users/{id}/follow', [UserController::class, 'follow'])->middleware(JwtAuthentication::class);
Route::delete('/users/{id}/unfollow', [UserController::class, 'unfollow'])->middleware(JwtAuthentication::class);

// Rutas para obtener seguidores y seguidos (públicas)
Route::get('/users/{id}/followers', [UserController::class, 'followers']);
Route::get('/users/{id}/following', [UserController::class, 'following']);
// --- FIN: NUEVAS RUTAS DE FOLLOW ---
Route::get('/users/{id}', [UserController::class, 'show']);

// Ruta para el Feed de Jophiel
Route::get('/feed', [FeedController::class, 'getFeed'])->middleware(JwtAuthentication::class);

// --- Rutas de Contenido (CRUD Público y Autenticado) ---
Route::get('/contents', [ContentController::class, 'index']);
// Nueva ruta para obtener la cantidad de likes (debe ir antes de la ruta {slug} para evitar colisión)
Route::get('/contents/{id}/likes', [ContentController::class, 'likes']);
Route::get('/contents/{id}/likes/users', [ContentController::class, 'likeUsers']);
Route::get('/contents/{slug}', [ContentController::class, 'show']);

Route::group('/contents', function () {
    Route::post('', [ContentController::class, 'store']);
    Route::post('/{id}', [ContentController::class, 'update']);
    Route::delete('/{id}', [ContentController::class, 'destroy']);
    Route::post('/{id}/like', [ContentController::class, 'toggleLike']);
})->middleware(JwtAuthentication::class);


// --- Rutas de Comentarios (Autenticado) ---
// Ruta pública para listar los comentarios de un contenido
Route::get('/comments/{content_id}', [CommentController::class, 'index']);

Route::group('/comments', function () {
    Route::post('/{content_id}', [CommentController::class, 'store']);
    Route::delete('/{comment_id}', [CommentController::class, 'destroy']);
})->middleware(JwtAuthentication::class);


// --- Rutas de Media ---
Route::post('/media', [MediaController::class, 'store'])->middleware(JwtAuthentication::class);
Route::get('/media/{id}', [MediaController::class, 'show']);


// --- Rutas de Administración (Protegidas por Permisos Granulares) ---
Route::group('/admin', function () {
    // El rol 'admin' tiene el permiso '*' y pasará todas estas verificaciones.
    // Otros roles (como 'editor') necesitarán estos permisos explícitamente.

    // --- ORDEN DE RUTAS CORREGIDO ---
    // Las rutas específicas/estáticas se definen ANTES de las rutas dinámicas/variables.
    Route::get('/contents/filter-by-data', [ContentController::class, 'filterByData'])->middleware(new PermissionMiddleware('admin.content.list'));
    Route::get('/contents/by-hash/{hash}', [ContentController::class, 'findByHash'])->middleware(new PermissionMiddleware('admin.content.list'));
    Route::get('/contents', [ContentController::class, 'indexAdmin'])->middleware(new PermissionMiddleware('admin.content.list'));
    Route::get('/contents/{id}', [ContentController::class, 'showAdmin'])->middleware(new PermissionMiddleware('admin.content.view'));
    // --- FIN DE LA CORRECCIÓN ---

    Route::get('/media', [MediaController::class, 'index'])->middleware(new PermissionMiddleware('admin.media.list'));
    Route::delete('/media/{id}', [MediaController::class, 'destroy'])->middleware(new PermissionMiddleware('admin.media.delete'));

    Route::post('/users/{id}/role', [UserController::class, 'changeRole'])->middleware(new PermissionMiddleware('admin.user.role.change'));

    Route::post('/options', [OptionController::class, 'updateBatch'])->middleware(new PermissionMiddleware('admin.options.update'));

    // Grupo para Gestión de Roles
    Route::group('/roles', function () {
        Route::get('', [RoleController::class, 'index'])->middleware(new PermissionMiddleware('admin.roles.list'));
        Route::post('', [RoleController::class, 'store'])->middleware(new PermissionMiddleware('admin.roles.create'));
        Route::post('/{id}', [RoleController::class, 'update'])->middleware(new PermissionMiddleware('admin.roles.update'));
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware(new PermissionMiddleware('admin.roles.delete'));
    });

    // Grupo para Gestión de Webhooks
    Route::group('/webhooks', function () {
        Route::get('', [WebhookController::class, 'index'])->middleware(new PermissionMiddleware('admin.webhooks.list'));
        Route::post('', [WebhookController::class, 'store'])->middleware(new PermissionMiddleware('admin.webhooks.create'));
        Route::post('/{id}', [WebhookController::class, 'update'])->middleware(new PermissionMiddleware('admin.webhooks.update'));
        Route::delete('/{id}', [WebhookController::class, 'destroy'])->middleware(new PermissionMiddleware('admin.webhooks.delete'));
    });
})->middleware([
    JwtAuthentication::class // Todas las rutas de admin requieren autenticación.
]);