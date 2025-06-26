<?php

use support\Request;

return [
    'debug' => (bool)env('APP_DEBUG', true),
    'error_reporting' => E_ALL,
    'default_timezone' => 'UTC',
    'request_class' => Request::class,
    'public_path' => base_path() . DIRECTORY_SEPARATOR . 'public',
    'runtime_path' => base_path(false) . DIRECTORY_SEPARATOR . 'runtime',
    'controller_suffix' => '', 
    'controller_reuse' => false,
];