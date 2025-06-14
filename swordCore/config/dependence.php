<?php

return [
    // Aquí puedes añadir tus definiciones.
    // Ejemplo:
    // App\service\UserService::class => function() {
    //     return new App\service\UserService(new \App\model\User);
    // }


    // Le decimos al contenedor cómo construir PaginaController.
    // Cuando se pida un PaginaController, primero debe crear un PaginaService
    // y pasarlo al constructor del controlador.
    \App\controller\PaginaController::class => function ($container) {
        return new \App\controller\PaginaController(
            $container->make(\App\service\PaginaService::class)
        );
    },

        // Le decimos al contenedor cómo construir PaginaPublicaController.
    \App\controller\PaginaPublicaController::class => function ($container) {
        return new \App\controller\PaginaPublicaController(
            $container->make(\App\service\PaginaService::class)
        );
    }
];