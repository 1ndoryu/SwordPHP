<?php

namespace app\bootstrap;

use Illuminate\Database\Capsule\Manager as Capsule;
use Throwable;
use support\Log;

class Database
{
    public static function start($worker)
    {
        // LA CONDICIÓN 'if (!$worker)' HA SIDO ELIMINADA.
        // La base de datos ahora se inicializará para los workers del servidor y para los scripts de CLI (tests).

        try {
            $capsule = new Capsule;
            $config = config('database');

            if ($config && isset($config['default'], $config['connections'][$config['default']])) {
                $defaultConnectionName = $config['default'];
                $connectionConfig = $config['connections'][$defaultConnectionName];

                $capsule->addConnection($connectionConfig);

                $capsule->setAsGlobal();
                $capsule->bootEloquent();
            } else {
                Log::channel('master')->warning('Configuración de la base de datos no encontrada o incompleta.');
            }
        } catch (Throwable $e) {
            Log::channel('master')->critical('Error fatal al inicializar la base de datos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}