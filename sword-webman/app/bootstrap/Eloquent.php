<?php

namespace App\bootstrap;

use Illuminate\Database\Capsule\Manager as Capsule;
use Webman\Bootstrap as WebmanBootstrap;

class Eloquent implements WebmanBootstrap
{
    /**
     * Inicia la cápsula de Eloquent para que esté disponible globalmente.
     *
     * @param $worker
     * @return void
     */
    public static function start($worker): void
    {
        // Solo inicializamos la base de datos en los procesos worker, no en el proceso monitor.
        if ($worker) {
            $config = config('database');
            $defaultConnection = $config['default'];
            $connections = $config['connections'];

            $capsule = new Capsule;
            // Agrega la conexión por defecto.
            $capsule->addConnection($connections[$defaultConnection]);

            // Permite que Eloquent utilice el despachador de eventos.
            // $capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));

            // Hace que esta instancia de Capsule esté disponible globalmente a través de métodos estáticos.
            $capsule->setAsGlobal();

            // Inicia Eloquent ORM.
            $capsule->bootEloquent();
        }
    }
}