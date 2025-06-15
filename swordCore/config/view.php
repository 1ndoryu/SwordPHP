<?php

use support\view\NativePhpView; // <-- Cambiamos la clase importada

/**
 * Configuración del motor de vistas.
 *
 * Fichero ajustado para usar plantillas PHP nativas
 * y mantener la estructura de temas.
 */

// Se mantiene la lógica para obtener la ruta del tema activo.
$themeConfig = config('theme', ['active_theme' => 'sword-theme-default']);
$activeTheme = $themeConfig['active_theme'];

// Se mantiene la definición de las rutas de las vistas, priorizando el tema.
// La clase NativePhpView usará estas mismas rutas para encontrar los archivos .php
$viewPaths = [
    // Busca primero en el directorio del tema activo.
    str_replace(['/', '\\'], DIRECTORY_SEPARATOR, SWORD_THEMES_PATH . '/' . $activeTheme),
    // Luego busca en el directorio de vistas del core.
    str_replace(['/', '\\'], DIRECTORY_SEPARATOR, app_path() . '/view'),
];

return [
    // 1. El cambio principal: apuntamos a nuestro nuevo renderizador.
    'handler' => NativePhpView::class,
    
    'options' => [
        // 2. Mantenemos tu definición de 'view_path' ya que es correcta.
        'view_path' => $viewPaths,

        // 3. 'cache_path' y 'namespaces' se eliminan.
        //    No son necesarios para plantillas PHP nativas.
    ]
];