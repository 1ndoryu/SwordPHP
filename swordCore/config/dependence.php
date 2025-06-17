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
            $container->make(\App\service\OpcionService::class) // <--- ESTA LÍNEA SE AÑADIÓ
        );
    },

    // Le decimos al contenedor cómo construir PaginaPublicaController.
    \App\controller\PaginaPublicaController::class => function ($container) {
        return new \App\controller\PaginaPublicaController(
            $container->make(\App\service\PaginaService::class)
        );
    }
];