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
    // Global middleware
    'global' => [
        // Se añade el middleware de sesión para que se ejecute en todas las peticiones.
        // Esto es crucial para la gestión de sesiones y la protección CSRF.
        Webman\Middleware\Session::class
    ],
    // Middleware that acts on the app
    '' => [
        App\middleware\StaticFile::class
    ]
];