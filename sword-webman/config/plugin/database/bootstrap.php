<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Webman\Bootstrap;

return new class implements Bootstrap
{
    public static function start($worker)
    {
        if ($worker) {
            $config = config('database');
            $connections = $config['connections'];
            $default_connection = $config['default'];

            $capsule = new Capsule;
            $capsule->addConnection($connections[$default_connection]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }
    }
};