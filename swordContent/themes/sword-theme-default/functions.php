<?php
function mi_funcion_de_prueba() {
    return "¡Functions.php del tema cargado con éxito!";
}

/**
 * Encola los assets (estilos y scripts) para el tema por defecto.
 *
 * Esta función se ejecuta cuando el controlador carga la página pública,
 * momento en el cual todas las constantes y servicios del framework ya están disponibles.
 */
function encolarAssets() {
    
    // EJEMPLO 1: Encolar un único archivo CSS usando el helper.
    // Se asume que existe un archivo style.css dentro de una carpeta /assets/css/
    encolarEstilo(
        'sword-theme-default-style', 
        rutaTema('assets/css/style.css')
    );

    // EJEMPLO 2: Encolar una CARPETA ENTERA de assets.
    // Para esto, necesitamos la ruta absoluta en el servidor, no una URL.
    $rutaCarpeta = SWORD_CONTENT_PATH . '/themes/' . config('theme.active_theme') . '/assets';
    
    // Es una buena práctica verificar si el directorio realmente existe.
    if (is_dir($rutaCarpeta)) {
        // Usamos el servicio directamente para encolar toda la carpeta.
        // Este método buscará y añadirá todos los archivos .css y .js que encuentre.
        assetService()->encolarDirectorio($rutaCarpeta);
    }

    /*
    // Ejemplo de cómo encolar un script individual:
    encolarAssets(
        'sword-theme-default-script',
        rutaTema('assets/js/main.js')
    );
    */
}

encolarAssets();
