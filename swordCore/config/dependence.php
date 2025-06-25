<?php

return [
    // Aquí puedes añadir tus definiciones.
    // Ejemplo:
    // App\service\UserService::class => function() {
    //     return new App\service\UserService(new \App\model\User);
    // }

    app\service\PermisoService::class => function ($container) {
        return new app\service\PermisoService(
            $container->make(app\service\OpcionService::class)
        );
    },

    app\service\ManagedContentService::class => function ($container) {
        return new app\service\ManagedContentService();
    },

    // Le decimos al contenedor cómo construir PaginaController.
    // Cuando se pida un PaginaController, ahora le pasamos TODOS los servicios.
    \App\controller\PaginaController::class => function ($container) {
        return new \App\controller\PaginaController(
            $container->make(\App\service\PaginaService::class),
            $container->make(\App\service\OpcionService::class),
            $container->make(\App\service\TemaService::class),
            $container->make(app\service\ManagedContentService::class)
        );
    },

    // Le decimos al contenedor cómo construir PaginaPublicaController.
    \App\controller\PluginController::class => function ($container) {
        return new \App\controller\PluginController(
            $container->make(\App\service\PluginService::class),
            $container->make(\App\service\OpcionService::class)
        );
    },
];
