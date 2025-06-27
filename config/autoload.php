<?php
// ARCHIVO CORREGIDO: config/autoload.php

return [
    'files' => [
        base_path() . '/app/functions.php',
        // Las siguientes dos líneas fueron eliminadas porque causaban un
        // conflicto de autocarga con el estándar PSR-4 definido en composer.json.
        // base_path() . '/support/Request.php',
        // base_path() . '/support/Response.php',
    ]
];
