<?php

use App\service\PluginPageService;
use App\service\OpcionService;

if (!function_exists('addPageAdmin')) {
    /**
     * Registra una página de administración para un plugin.
     * Es un wrapper para el PluginPageService.
     *
     * @param string $slug El slug único para la página (ej: 'mi-plugin-ajustes').
     * @param array $opciones Un array con la configuración de la página.
     * 'page_title' => (string) El título que se mostrará en la cabecera (H1).
     * 'callback'   => (callable) La función que renderiza el contenido de la página.
     */
    function addPageAdmin(string $slug, array $opciones)
    {
        PluginPageService::getInstancia()->registrar($slug, $opciones);
    }
}

if (!function_exists('getOptionPlugin')) {
    /**
     * Obtiene una opción guardada por un plugin.
     * Añade un prefijo automático para evitar colisiones.
     *
     * @param string $slugPlugin El slug del plugin que guarda la opción.
     * @param string $nombreOpcion El nombre de la opción.
     * @param mixed $valorPorDefecto Valor a devolver si la opción no existe.
     * @return mixed
     */
    function getOptionPlugin(string $slugPlugin, string $nombreOpcion, $valorPorDefecto = null)
    {
        $opcionService = new OpcionService();
        $clavePrefijada = "plugin_{$slugPlugin}_{$nombreOpcion}";
        return $opcionService->getOption($clavePrefijada, $valorPorDefecto);
    }
}

if (!function_exists('updateOptionPlugin')) {
    /**
     * Guarda una opción para un plugin.
     * Añade un prefijo automático para evitar colisiones.
     *
     * @param string $slugPlugin El slug del plugin que guarda la opción.
     * @param string $nombreOpcion El nombre de la opción.
     * @param mixed $valor El valor a guardar.
     * @return bool
     */
    function updateOptionPlugin(string $slugPlugin, string $nombreOpcion, $valor): bool
    {
        $opcionService = new OpcionService();
        $clavePrefijada = "plugin_{$slugPlugin}_{$nombreOpcion}";
        return $opcionService->updateOption($clavePrefijada, $valor);
    }
}