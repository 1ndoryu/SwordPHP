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
        // REVERTIDO: Volvemos al método de singleton manual.
        // Esto es crucial porque este helper puede ser llamado desde archivos de configuración
        // antes de que el contenedor de dependencias principal esté inicializado.
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
        // Se usa el TemaService para obtener dinámicamente el tema activo desde la BD.
        $themeDir = '/swordContent/themes/' . \App\service\TemaService::getActiveTheme();
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

if (!function_exists('encolarRecursos')) {
    /**
     * Encola un archivo CSS/JS o todos los archivos de un directorio desde el tema activo.
     *
     * @param string $ruta Ruta relativa al archivo o directorio dentro del tema activo.
     * @return void
     */
    function encolarRecursos(string $ruta): void
    {
        // REVERTIDO: Restauramos la instanciación de TemaService. Aunque el método que usa es estático,
        // revertimos el archivo a su estado original completo para garantizar la estabilidad.
        $temaService = new \App\service\TemaService();
        $rutaTemaAbsoluto = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $temaService->getActiveTheme();
        $rutaRecursoAbsoluto = $rutaTemaAbsoluto . DIRECTORY_SEPARATOR . ltrim($ruta, '/');

        $urlTema = rutaTema();

        if (is_dir($rutaRecursoAbsoluto)) {
            try {
                $iterador = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rutaRecursoAbsoluto, \RecursiveDirectoryIterator::SKIP_DOTS));

                foreach ($iterador as $archivo) {
                    if ($archivo->isFile()) {
                        $extension = strtolower($archivo->getExtension());
                        $rutaRelativaArchivo = ltrim(str_replace($rutaTemaAbsoluto, '', $archivo->getPathname()), '/\\');
                        $urlRecurso = $urlTema . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $rutaRelativaArchivo);
                        $identificador = 'recurso-' . pathinfo($archivo->getFilename(), PATHINFO_FILENAME);

                        if ($extension === 'css') {
                            encolarEstilo($identificador, $urlRecurso);
                        } elseif ($extension === 'js') {
                            encolarScript($identificador, $urlRecurso);
                        }
                    }
                }
            } catch (\Exception $e) {
                \support\Log::error("Error al encolar directorio de recursos '{$ruta}': " . $e->getMessage());
            }
        } elseif (is_file($rutaRecursoAbsoluto)) {
            $extension = strtolower(pathinfo($rutaRecursoAbsoluto, PATHINFO_EXTENSION));
            $urlRecurso = $urlTema . '/' . ltrim($ruta, '/');
            $identificador = 'recurso-' . pathinfo($rutaRecursoAbsoluto, PATHINFO_FILENAME);

            if ($extension === 'css') {
                encolarEstilo($identificador, $urlRecurso);
            } elseif ($extension === 'js') {
                encolarScript($identificador, $urlRecurso);
            }
        }
    }
}