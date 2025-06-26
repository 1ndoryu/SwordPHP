<?php

// Usar nuestra propia clase de arranque para la base de datos
use app\bootstrap\Database;

return [
    Database::class, // <-- Esta es la línea clave
    
    support\bootstrap\Session::class,
];