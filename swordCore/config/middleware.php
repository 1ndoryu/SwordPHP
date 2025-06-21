<?php

$globalMiddleware = [
    // Middlewares base que se ejecutan siempre
    App\middleware\NormalizePathMiddleware::class,
    App\middleware\Session::class,
    App\middleware\StaticFile::class,
    app\middleware\XdebugProfiler::class,
];

// Añade el middleware para cargar plugins y el tema solo si el CMS está habilitado.
if (env('CMS_ENABLED', true)) {
    $globalMiddleware[] = App\middleware\IncludeLoadedPluginsMiddleware::class;
    $globalMiddleware[] = App\middleware\IncludeActiveThemeMiddleware::class;
}

return [
    // Middleware global. Se aplica a TODAS las rutas.
    '' => $globalMiddleware,
];