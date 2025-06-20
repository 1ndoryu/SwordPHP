<?php

$globalMiddleware = [
    // Middlewares base que se ejecutan siempre
    App\middleware\NormalizePathMiddleware::class,
    App\middleware\Session::class,
    App\middleware\StaticFile::class,
    app\middleware\XdebugProfiler::class,
];

// Añade el middleware para cargar plugins solo si el CMS está habilitado.
if (env('CMS_ENABLED', true)) {
    $globalMiddleware[] = App\middleware\IncludeLoadedPluginsMiddleware::class;
    $globalMiddleware[] = app\middleware\XdebugProfiler::class;
}

return [
    // Middleware global. Se aplica a TODAS las rutas.
    '' => $globalMiddleware,
];