<?php

// tests/bootstrap.php

// Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar el bootstrap principal de la aplicación.
// Esto inicializará DotEnv, la configuración, y crucialmente, la base de datos (Capsule).
require_once __DIR__ . '/../support/bootstrap.php';