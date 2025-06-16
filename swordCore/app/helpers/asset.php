<?php

/**
 * Archivo de helpers para la gestión de assets (CSS/JS) y rutas de contenido.
 */

if (!function_exists('assetService')) {
    /**
     * Obtiene la instancia única del servicio de assets.
     *
     * @return \App\service\AssetService La instancia del servicio de assets.
     */
    function assetService(): \App\service\AssetService
    {
        static $instancia = null;

        if ($instancia === null) {
            $instancia = new \App\service\AssetService();
        }

        return $instancia;
    }
}

if (!function_exists('encolarEstilo')) {
    /**
     * Encola una hoja de estilos para ser incluida en el head de la página.
     *
     * @param string $identificador Un nombre único para el estilo.
     * @param string $ruta La ruta pública al archivo CSS.
     */
    function encolarEstilo(string $identificador, string $ruta): void
    {
        assetService()->encolarCss($identificador, $ruta);
    }
}

if (!function_exists('encolarScript')) {
    /**
     * Encola un script de JavaScript para ser incluido en el footer de la página.
     *
     * @param string $identificador Un nombre único para el script.
     * @param string $ruta La ruta pública al archivo JS.
     */
    function encolarScript(string $identificador, string $ruta): void
    {
        assetService()->encolarJs($identificador, $ruta);
    }
}

if (!function_exists('rutaTema')) {
    /**
     * Devuelve la URL pública completa al directorio del tema activo.
     *
     * @param string $rutaAdicional Ruta opcional para añadir al final de la URL del tema.
     * @return string
     */
    function rutaTema(string $rutaAdicional = ''): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $themeDir = '/swordContent/themes/' . config('theme.active_theme');
        $finalPath = $baseUrl . $themeDir;

        if ($rutaAdicional) {
            $finalPath .= '/' . ltrim($rutaAdicional, '/');
        }

        return $finalPath;
    }
}

if (!function_exists('url_contenido')) {
    /**
     * Genera una URL relativa a la raíz para un recurso dentro de swordContent.
     *
     * @param string $ruta La ruta relativa al recurso desde la raíz de swordContent.
     * @return string La URL relativa completa (ej: /swordContent/media/archivo.jpg).
     */
    function url_contenido(string $ruta = ''): string
    {
        $basePath = '/swordContent';
        $rutaLimpia = ltrim($ruta, '/');
        return rtrim($basePath, '/') . '/' . $rutaLimpia;
    }
}
