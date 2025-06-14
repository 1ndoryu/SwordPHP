<?php

namespace App\bootstrap;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
// Se crea un alias para el Dispatcher para evitar colisiones de namespace.
use Illuminate\Events\Dispatcher as IlluminateDispatcher;
use Illuminate\Pagination\Paginator;
use Webman\Bootstrap;

class Eloquent implements Bootstrap
{
    public static function start($worker)
    {
        // --- Configuración de la Base de Datos ---
        $capsule = new Capsule;
        $connections = config('database.connections');
        $defaultConnectionName = config('database.default');

        // Registrar todas las conexiones por su nombre.
        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        // Asegurar que la conexión por defecto también esté registrada bajo el nombre literal 'default'.
        if (isset($connections[$defaultConnectionName])) {
            $capsule->addConnection($connections[$defaultConnectionName], 'default');
        }

        // Usar el dispatcher con alias para instanciar la clase correcta de Illuminate.
        $capsule->setEventDispatcher(new IlluminateDispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        
        // --- Configuración Robusta del Paginador de Laravel ---

        // El problema de "Call to undefined function app()" o TypeErrors en el paginador
        // se debe a que este bootstrap se ejecuta ANTES de que los helpers globales estén disponibles.
        // La solución es construir manualmente la fábrica de vistas de Blade.
        Paginator::viewFactoryResolver(function () {
            // Leemos la misma configuración de vistas que usa la aplicación.
            $config = config('view.options');
            
            // Creamos la instancia de Blade.
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
        
        // Se define cómo el paginador obtiene la ruta actual.
        Paginator::currentPathResolver(function () {
            return request()->path();
        });

        // Se define cómo el paginador obtiene el número de página actual.
        Paginator::currentPageResolver(function ($pageName = 'page') {
            return request()->input($pageName, 1);
        });

        // Establecemos las vistas de Bootstrap 5 por defecto para la paginación.
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');
    }
}