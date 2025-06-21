<?php

namespace App\service;

use support\Config;

/**
 * Servicio para gestionar los temas.
 *
 * Se encarga de escanear, parsear y gestionar los temas disponibles,
 * y también actúa como la fuente de verdad para saber cuál es el tema activo.
 */
class TemaService
{
    /**
     * Cache estático para el slug del tema activo.
     * @var string|null
     */
    private static ?string $activeThemeSlug = null;

    /**
     * Obtiene el slug del tema activo.
     *
     * Lo busca en la base de datos la primera vez y lo guarda en un caché estático
     * para las siguientes llamadas dentro de la misma petición.
     *
     * @return string
     */
    public static function getActiveTheme(): string
    {
        if (self::$activeThemeSlug === null) {
            $opcionService = new OpcionService();
            // Usa el valor del archivo de configuración como fallback.
            $fallbackTheme = config('theme.active_theme_fallback', 'sword-theme-default');
            self::$activeThemeSlug = $opcionService->getOption('active_theme', $fallbackTheme);
        }
        return self::$activeThemeSlug;
    }

    /**
     * Obtiene la lista de todos los temas disponibles en el directorio de temas.
     *
     * Itera sobre el directorio definido en SWORD_THEMES_PATH, identifica los temas válidos
     * (aquellos con un style.css) y extrae su información.
     *
     * @return array Un array de temas, donde cada tema es un array asociativo con sus datos.
     */
    public function obtenerTemasDisponibles(): array
    {
        $temas = [];
        $directorioTemas = SWORD_THEMES_PATH;

        if (!is_dir($directorioTemas)) {
            \support\Log::error("La ruta de temas no es un directorio válido o no se puede leer: " . $directorioTemas);
            return [];
        }

        $directorios = array_filter(scandir($directorioTemas), fn($dir) => !in_array($dir, ['.', '..']));

        foreach ($directorios as $slug) {
            $rutaTema = $directorioTemas . DIRECTORY_SEPARATOR . $slug;
            $rutaCss = $rutaTema . DIRECTORY_SEPARATOR . 'style.css';

            if (is_dir($rutaTema) && file_exists($rutaCss)) {
                $datosTema = $this->parsearCabeceraTema($rutaCss);
                if (!empty($datosTema['nombre'])) {
                    $datosTema['slug'] = $slug;
                    $datosTema['screenshot'] = file_exists($rutaTema . '/screenshot.png')
                        ? url_contenido("themes/{$slug}/screenshot.png")
                        : null;
                    $temas[$slug] = $datosTema;
                }
            }
        }

        return $temas;
    }

    /**
     * Parsea la cabecera de un archivo style.css para extraer sus metadatos.
     *
     * Lee los primeros 4KB del archivo y utiliza expresiones regulares para encontrar
     * los campos de la cabecera del tema.
     *
     * @param string $rutaArchivoCss La ruta completa al archivo style.css.
     * @return array Un array con los datos extraídos de la cabecera.
     */
    private function parsearCabeceraTema(string $rutaArchivoCss): array
    {
        $contenido = file_get_contents($rutaArchivoCss, false, null, 0, 4096);

        $cabeceras = [
            'nombre'   => 'Theme Name',
            'uri'    => 'Theme URI',
            'descripcion' => 'Description',
            'autor'   => 'Author',
            'uriAutor'  => 'Author URI',
            'version'  => 'Version',
        ];

        $datosTema = [];
        foreach ($cabeceras as $clave => $regex) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $contenido, $match) && $match[1]) {
                $datosTema[$clave] = trim($match[1]);
            } else {
                $datosTema[$clave] = '';
            }
        }

        return $datosTema;
    }

    /**
     * Activa un tema específico, lo guarda en la base de datos y desencadena una recarga del servidor.
     *
     * @param string $slug El identificador (directorio) del tema a activar.
     * @return bool Devuelve true si la operación fue exitosa.
     * @throws \Exception Si el tema no existe o si hay problemas de guardado.
     */
    public function activarTema(string $slug): bool
    {
        // 1. Validar que el tema a activar realmente existe.
        $temasDisponibles = $this->obtenerTemasDisponibles();
        if (!isset($temasDisponibles[$slug])) {
            throw new \Exception("El tema '{$slug}' no es un tema válido o no se pudo encontrar.");
        }

        // 2. Guardar el nuevo tema activo en la base de datos.
        $opcionService = new OpcionService();
        $guardado = $opcionService->updateOption('active_theme', $slug);

        if ($guardado) {
            // "Tocar" un archivo de configuración para que el monitor de archivos de Webman
            // detecte un cambio y recargue los workers.
            $monitorOptions = config('process.monitor.constructor.options', []);
            if (!empty($monitorOptions['enable_file_monitor'])) {
                @touch(config_path('theme.php'));
            }
            // Limpiamos el caché estático para que la siguiente llamada a getActiveTheme() obtenga el nuevo valor.
            self::$activeThemeSlug = null;
        }

        return $guardado;
    }

    /**
     * Obtiene la lista de plantillas de página disponibles en el tema activo.
     *
     * @return array Un array asociativo [nombre_archivo => nombre_plantilla].
     */
    public function obtenerPlantillasDePagina(): array
    {
        $plantillas = [];
        $temaActivoSlug = self::getActiveTheme();
        $rutaTemaActivo = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $temaActivoSlug;

        if (!is_dir($rutaTemaActivo)) {
            return [];
        }

        try {
            $iterador = new \DirectoryIterator($rutaTemaActivo);
            foreach ($iterador as $archivoInfo) {
                if ($archivoInfo->isFile() && $archivoInfo->getExtension() === 'php') {
                    $contenido = file_get_contents($archivoInfo->getPathname(), false, null, 0, 8192);
                    if (preg_match('/^[ \t\/*#@]*Template Name:(.*)$/mi', $contenido, $match)) {
                        if (!empty($match[1])) {
                            $nombrePlantilla = trim($match[1]);
                            $nombreArchivo = $archivoInfo->getFilename();
                            $plantillas[$nombreArchivo] = $nombrePlantilla;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \support\Log::error("Error al escanear plantillas de página en el tema '{$temaActivoSlug}': " . $e->getMessage());
            return [];
        }

        asort($plantillas);

        return $plantillas;
    }
}