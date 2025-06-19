<?php

namespace App\service;

use support\Config;

/**
 * Servicio para gestionar los temas.
 *
 * Se encarga de escanear, parsear y gestionar los temas disponibles
 * en la instalación de SwordPHP.
 */
class TemaService
{
    private OpcionService $opcionService;

    /**
     * Inyecta el servicio de opciones para interactuar con la base de datos.
     *
     * @param OpcionService $opcionService
     */
    public function __construct(OpcionService $opcionService)
    {
        $this->opcionService = $opcionService;
    }

    /**
     * Obtiene el slug del tema activo directamente desde la base de datos.
     *
     * @return string
     */
    public function obtenerTemaActivoSlug(): string
    {
        // Usa el valor del archivo de configuración como fallback si la opción no está en la BD.
        $fallbackTheme = config('theme.active_theme', 'sword-theme-default');
        return $this->opcionService->obtenerOpcion('active_theme', $fallbackTheme);
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
            'nombre' => 'Theme Name',
            'uri'  => 'Theme URI',
            'descripcion' => 'Description',
            'autor' => 'Author',
            'uriAutor' => 'Author URI',
            'version' => 'Version',
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
     * Activa un tema guardando su slug como una opción en la base de datos.
     *
     * @param string $slug El identificador del tema a activar.
     * @return bool Devuelve true si la operación fue exitosa.
     * @throws \Exception Si el tema no existe.
     */
    public function activarTema(string $slug): bool
    {
        // 1. Validar que el tema a activar realmente existe.
        $temasDisponibles = $this->obtenerTemasDisponibles();
        if (!isset($temasDisponibles[$slug])) {
            throw new \Exception("El tema '{$slug}' no es un tema válido o no se pudo encontrar.");
        }

        // 2. Guardar la nueva opción en la base de datos.
        $this->opcionService->guardarOpcion('active_theme', $slug);

        // 3. Actualizar la configuración en memoria para el worker actual.
        // Esto evita la necesidad de una recarga para que el cambio surta efecto en la petición actual.
        Config::set('theme.active_theme', $slug);

        // 4. Actualizar también las rutas de las vistas en memoria.
        $projectRoot = dirname(base_path());
        $newViewPaths = [
            SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $slug,
            app_path() . DIRECTORY_SEPARATOR . 'view',
        ];
        Config::set('view.options.view_path', $newViewPaths);

        return true;
    }

    /**
     * Obtiene la lista de plantillas de página disponibles en el tema activo.
     *
     * Escanea los archivos .php en el directorio raíz del tema activo y busca
     * una cabecera de comentario especial para identificarlas como plantillas.
     * Ejemplo de cabecera: /* Template Name: Mi Plantilla Personalizada * /
     *
     * @return array Un array asociativo [nombre_archivo => nombre_plantilla].
     */
    public function obtenerPlantillasDePagina(): array
    {
        $plantillas = [];
        $temaActivoSlug = $this->obtenerTemaActivoSlug();
        $rutaTemaActivo = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $temaActivoSlug;

        if (!is_dir($rutaTemaActivo)) {
            return [];
        }

        try {
            $iterador = new \DirectoryIterator($rutaTemaActivo);
            foreach ($iterador as $archivoInfo) {
                if ($archivoInfo->isFile() && $archivoInfo->getExtension() === 'php') {
                    // Leemos solo los primeros 8KB, que es más que suficiente para las cabeceras.
                    $contenido = file_get_contents($archivoInfo->getPathname(), false, null, 0, 8192);

                    // Expresión regular para buscar 'Template Name:' en un comentario de bloque PHP.
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

        // Ordenamos las plantillas por nombre para una mejor visualización en el selector.
        asort($plantillas);

        return $plantillas;
    }
}
