<?php

use App\service\ShortcodeService;

if (!function_exists('addShortcode')) {
    /**
     * Registra una función de callback para un shortcode.
     *
     * @param string $tag El nombre del shortcode a registrar.
     * @param callable $callback La función que se ejecutará para renderizar el shortcode.
     */
    function addShortcode(string $tag, callable $callback)
    {
        ShortcodeService::getInstancia()->registrar($tag, $callback);
    }
}

if (!function_exists('procesarShortcodes')) {
    /**
     * Procesa los shortcodes dentro de una cadena de contenido.
     * Esta función se engancha al filtro 'theContent'.
     *
     * @param string $contenido El contenido a procesar.
     * @return string El contenido con los shortcodes renderizados.
     */
    function procesarShortcodes(string $contenido): string
    {
        return ShortcodeService::getInstancia()->procesar($contenido);
    }
}

// Engancha la función de procesamiento al filtro 'theContent'.
// La prioridad 11 se usa para asegurar que se ejecute después de los filtros por defecto,
// como el de WordPress para 'wpautop', si se implementara en el futuro.
addFilter('theContent', 'procesarShortcodes', 11);