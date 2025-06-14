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

        // --- SOLUCIÓN ALTERNATIVA Y ROBUSTA PARA LA PAGINACIÓN ---

        // El problema de "Call to undefined function app()" se debe a que este bootstrap
        // se ejecuta ANTES de que los plugins (como webman/blade) hayan inicializado
        // sus servicios y helpers globales como app().
        
        // La solución es construir manualmente la fábrica de vistas de Blade,
        // sin depender de helpers que aún no existen en este punto del arranque.
        Paginator::viewFactoryResolver(function () {
            // Leemos la misma configuración de vistas que usa la aplicación desde config/view.php
            $config = config('view.options');
            
            // Creamos la instancia de Blade. Su clase es \Jenssegers\Blade\Blade
            $blade = new \Jenssegers\Blade\Blade(
                $config['view_path'], 
                $config['cache_path']
            );

            // Es CRUCIAL registrar los "namespaces" para que el paginador encuentre las vistas
            // que usan el prefijo `pagination::` (ej: 'pagination::bootstrap-5').
            foreach ($config['namespaces'] ?? [] as $namespace => $path) {
                $blade->addNamespace($namespace, $path);
            }
            
            // Esta instancia de Blade es la "fábrica de vistas" que el paginador necesita.
            return $blade;
        });
        
        // El resto de la configuración del paginador no necesita cambios.
        Paginator::currentPathResolver(function () {
            return request()->path();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            return request()->input($pageName, 1);
        });

        // Establecemos las vistas de Bootstrap 5 por defecto.
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');
    }
}