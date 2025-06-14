<?php

return [
    // Middleware global. Se aplica a TODAS las rutas.
    // ESTO NO SE USA PARA AGREGAR ALIAS DE MIDDLEWARE La forma correcta en Webman es crear la clase del middleware (lo que hicimos en el primer paso) y luego aplicarla directamente en el archivo de rutas. No se necesita ningún paso intermedio de registro.
    '' => [
        App\middleware\NormalizePathMiddleware::class,
        App\middleware\Session::class, 
        App\middleware\StaticFile::class
    ],
];