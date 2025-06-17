<?php

use App\service\PluginPageService;

if (!function_exists('agregarPaginaAdmin')) {
    /**
     * Registra una página de administración para un plugin.
     * Es un wrapper para el PluginPageService.
     *
     * @param string $slug El slug único para la página (ej: 'mi-plugin-ajustes').
     * @param array $opciones Un array con la configuración de la página.
     * 'page_title' => (string) El título que se mostrará en la cabecera (H1).
     * 'callback'   => (callable) La función que renderiza el contenido de la página.
     */
    function agregarPaginaAdmin(string $slug, array $opciones)
    {
        PluginPageService::getInstancia()->registrar($slug, $opciones);
    }
}
