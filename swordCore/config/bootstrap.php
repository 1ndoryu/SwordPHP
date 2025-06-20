<?php

$bootstrap = [
    support\bootstrap\Session::class,
    App\bootstrap\Eloquent::class,
];

// Carga el precargador de plugins activos solo si el CMS está habilitado.
if (env('CMS_ENABLED', true)) {
    $bootstrap[] = App\bootstrap\PreloadActivePlugins::class;
}

return $bootstrap;