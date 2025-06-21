<?php 

use support\Container;

if (!function_exists('support_path')) {
    /**
     * Obtiene la ruta absoluta al directorio 'support'.
     *
     * @param string $path Ruta adicional a concatenar.
     * @return string
     */
    function support_path(string $path = ''): string
    {
        // Usa la función base_path() de Webman para construir la ruta al directorio 'support'.
        $support_path = base_path() . DIRECTORY_SEPARATOR . 'support';

        // Si se proporciona una ruta adicional, la concatena.
        if ($path) {
            return $support_path . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        }

        return $support_path;
    }
}

if (!function_exists('container')) {
    /**
     * Obtiene una instancia del contenedor de dependencias.
     *
     * @param string $id
     * @return mixed
     */
    function container(string $id)
    {
        return Container::get($id);
    }
}