<?php

namespace app\bootstrap;

use Illuminate\Database\Capsule\Manager as Capsule;
use Throwable;

class Database
{
    public static function start($worker)
    {
        if (!$worker) {
            return;
        }

        try {
            $capsule = new Capsule;
            $config = config('database');

            if ($config && isset($config['default'], $config['connections'][$config['default']])) {
                $defaultConnectionName = $config['default'];
                $connectionConfig = $config['connections'][$defaultConnectionName];

                // --- INICIO DE LA CORRECCIÓN CLAVE ---
                // Al no pasar un segundo argumento, la conexión se registra como 'default',
                // que es lo que Eloquent busca.
                $capsule->addConnection($connectionConfig);
                // --- FIN DE LA CORRECCIÓN CLAVE ---

                $capsule->setAsGlobal();
                $capsule->bootEloquent();
            }
        } catch (Throwable $e) {
            // Esto es útil para depurar si la configuración falla.
            // Puedes revisar los logs de workerman si hay problemas.
            error_log('Error al inicializar la base de datos: ' . $e->getMessage());
        }
    }
}