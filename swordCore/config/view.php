<?php

use support\view\Blade;

return [
    'handler' => Blade::class,
    'options' => [
        'view_path' => app_path() . '/view',
        'cache_path' => runtime_path() . '/views',
        
        'namespaces' => [
            'pagination' => base_path() . '/vendor/illuminate/pagination/resources/views'
        ]
    ]
];