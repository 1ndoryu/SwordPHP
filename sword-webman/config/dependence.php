<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    // Aquí puedes añadir tus definiciones.
    // Ejemplo:
    // App\service\UserService::class => function() {
    //     return new App\service\UserService(new \App\model\User);
    // }

    // ============ INICIO: DEFINICIÓN AÑADIDA ============
    // Le decimos al contenedor cómo construir PaginaController.
    // Cuando se pida un PaginaController, primero debe crear un PaginaService
    // y pasarlo al constructor del controlador.
    \App\controller\PaginaController::class => function ($container) {
        return new \App\controller\PaginaController(
            $container->make(\App\service\PaginaService::class)
        );
    }
    // ============ FIN: DEFINICIÓN AÑADIDA ============
];