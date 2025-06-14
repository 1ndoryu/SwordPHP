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
    // Middleware global. Se aplica a TODAS las rutas.
    // ESTO NO SE USA PARA AGREGAR ALIAS DE MIDDLEWARE La forma correcta en Webman es crear la clase del middleware (lo que hicimos en el primer paso) y luego aplicarla directamente en el archivo de rutas. No se necesita ningún paso intermedio de registro.
    '' => [
        App\middleware\NormalizePathMiddleware::class,
        App\middleware\Session::class, 
        App\middleware\StaticFile::class
    ],
];