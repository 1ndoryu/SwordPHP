<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Webman\Bootstrap;

return new class implements Bootstrap
{
    /**
     * Bootstrap para el plugin webman/database.
     *
     * NOTA: La lógica de inicialización de Eloquent ha sido centralizada en
     * app/bootstrap/Eloquent.php para evitar configuraciones duplicadas y
     * resolver problemas de paginación. Este fichero se deja intencionadamente
     * vacío para no interferir.
     */
    public static function start($worker) {}
};
