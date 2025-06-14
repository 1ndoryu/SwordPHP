<?php

namespace app\bootstrap;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\Paginator;
use Webman\Bootstrap;

class Eloquent implements Bootstrap
{
    public static function start($worker)
    {
        $capsule = new Capsule;
        $connections = config('database.connections');

        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // --- DEJA SOLAMENTE ESTA CONFIGURACIÓN PARA EL PAGINADOR ---

        // 1. Le dice al Paginador que para renderizar, debe pedir la fábrica de vistas.
        Paginator::viewFactoryResolver(function () {
            // La fábrica ya está configurada gracias a nuestro cambio en config/view.php
            return view(); 
        });
        
        // 2. Le dice al Paginador cómo obtener la información de la URL actual.
        Paginator::currentPathResolver(function () {
            return request()->path();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            return request()->input($pageName, 1);
        });

        // 3. Le dice al Paginador qué estilo de plantilla usar.
        Paginator::useBootstrapFive();
    }
}