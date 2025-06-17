<?php

return [
    // Aquí puedes añadir tus definiciones.
    // Ejemplo:
    // App\service\UserService::class => function() {
    //     return new App\service\UserService(new \App\model\User);
    // }


    // Le decimos al contenedor cómo construir PaginaController.
    // Cuando se pida un PaginaController, ahora le pasamos AMBOS servicios.
    \App\controller\PaginaController::class => function ($container) {
        return new \App\controller\PaginaController(
            $container->make(\App\service\PaginaService::class),
            $container->make(\App\service\OpcionService::class),
            $container->make(\App\service\TemaService::class)
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
