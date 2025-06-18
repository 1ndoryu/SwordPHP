<?php

namespace App\bootstrap;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher as IlluminateDispatcher;
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

        // --- El código de configuración del paginador de Laravel se ha eliminado ---
        //
        // Ya no es necesario configurar Paginator::viewFactoryResolver, currentPathResolver, etc.,
        // porque la aplicación ahora utiliza una función auxiliar de PHP nativo
        // llamada renderizarPaginacion() para generar los enlaces de paginación.
        // Esto elimina la dependencia de Blade para esta funcionalidad.
    }
}
