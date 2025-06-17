<?php

namespace App\service;

use support\Log;

/**
 * Servicio para la gestión de plugins.
 *
 * Se encarga de descubrir, parsear y gestionar los plugins disponibles
 * en la instalación de SwordPHP.
 */
class PluginService
{
    /**
     * Obtiene la lista de todos los plugins disponibles en el directorio `swordContent/plugins`.
     *
     * Itera sobre el directorio, identifica los plugins válidos (aquellos con un
     * archivo principal `slug/slug.php`) y extrae su información.
     *
     * @return array Un array de plugins, donde cada plugin es un array asociativo con sus datos.
     */
    public function obtenerPluginsDisponibles(): array
    {
        $plugins = [];
        $directorioPlugins = SWORD_PLUGINS_PATH;

        // Asegurarse de que el directorio de plugins exista.
        if (!is_dir($directorioPlugins)) {
            if (!mkdir($directorioPlugins, 0755, true)) {
                 Log::error("No se pudo crear el directorio de plugins en: $directorioPlugins");
                 return [];
            }
        }
        
        $directorios = array_filter(scandir($directorioPlugins), fn($dir) => !in_array($dir, ['.', '..']));

        foreach ($directorios as $slug) {
            $rutaPlugin = $directorioPlugins . DIRECTORY_SEPARATOR . $slug;
            // La convención es que el archivo principal se llame igual que el directorio del plugin.
            $archivoPrincipal = $rutaPlugin . DIRECTORY_SEPARATOR . $slug . '.php';

            if (is_dir($rutaPlugin) && file_exists($archivoPrincipal)) {
                $datosPlugin = $this->parsearCabeceraPlugin($archivoPrincipal);
                if (!empty($datosPlugin['nombre'])) {
                    $datosPlugin['slug'] = $slug;
                    $datosPlugin['archivoPrincipal'] = $slug . '.php';
                    $plugins[$slug] = $datosPlugin;
                }
            }
        }

        return $plugins;
    }

    /**
     * Parsea la cabecera de un archivo PHP para extraer los metadatos del plugin.
     *
     * Lee los primeros 8KB del archivo y utiliza expresiones regulares para encontrar
     * los campos de la cabecera del plugin.
     *
     * @param string $rutaArchivoPHP La ruta completa al archivo principal del plugin.
     * @return array Un array con los datos extraídos de la cabecera.
     */
    private function parsearCabeceraPlugin(string $rutaArchivoPHP): array
    {
        $contenido = file_get_contents($rutaArchivoPHP, false, null, 0, 8192);

        $cabeceras = [
            'nombre'      => 'Plugin Name',
            'uri'         => 'Plugin URI',
            'descripcion' => 'Description',
            'version'     => 'Version',
            'autor'       => 'Author',
            'uriAutor'    => 'Author URI',
        ];

        $datosPlugin = [];
        foreach ($cabeceras as $clave => $regex) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $contenido, $match) && $match[1]) {
                $datosPlugin[$clave] = trim($match[1]);
            } else {
                $datosPlugin[$clave] = '';
            }
        }

        return $datosPlugin;
    }

    /**
     * Activa un plugin añadiéndolo a la lista de plugins activos en la base de datos.
     *
     * @param string $slug El slug del plugin a activar.
     * @return bool
     * @throws \Exception Si el plugin a activar no existe.
     */
    public function activarPlugin(string $slug): bool
    {
        $opcionService = new OpcionService();
        $pluginsDisponibles = $this->obtenerPluginsDisponibles();

        if (!isset($pluginsDisponibles[$slug])) {
            throw new \Exception("El plugin '{$slug}' no es un plugin válido.");
        }

        $pluginsActivos = $opcionService->obtenerOpcion('active_plugins', []);
        
        if (!in_array($slug, $pluginsActivos)) {
            $pluginsActivos[] = $slug;
            return $opcionService->guardarOpcion('active_plugins', $pluginsActivos);
        }

        return true; // Ya estaba activo, la operación se considera exitosa.
    }

    /**
     * Desactiva un plugin eliminándolo de la lista de plugins activos.
     *
     * @param string $slug El slug del plugin a desactivar.
     * @return bool
     */
    public function desactivarPlugin(string $slug): bool
    {
        $opcionService = new OpcionService();
        $pluginsActivos = $opcionService->obtenerOpcion('active_plugins', []);

        $key = array_search($slug, $pluginsActivos);

        if ($key !== false) {
            unset($pluginsActivos[$key]);
            $pluginsActivos = array_values($pluginsActivos); // Re-indexar array
            return $opcionService->guardarOpcion('active_plugins', $pluginsActivos);
        }

        return true; // No estaba activo, la operación se considera exitosa.
    }
}