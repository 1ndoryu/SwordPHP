<?php

namespace App\service;

use support\Log;
use App\service\PluginRegistry;

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

        if (!is_dir($directorioPlugins)) {
            if (!mkdir($directorioPlugins, 0755, true)) {
                Log::error("No se pudo crear el directorio de plugins en: $directorioPlugins");
                return [];
            }
        }

        $directorios = array_filter(scandir($directorioPlugins), fn($dir) => !in_array($dir, ['.', '..']));

        foreach ($directorios as $slug) {
            $rutaPlugin = $directorioPlugins . DIRECTORY_SEPARATOR . $slug;
            if (!is_dir($rutaPlugin)) {
                continue;
            }

            $archivoPrincipal = null;
            $posiblesNombres = [
                $slug . '.php',
                ucfirst($slug) . '.php'
            ];

            $archivosPhp = glob($rutaPlugin . '/*.php');
            if ($archivosPhp) {
                foreach ($archivosPhp as $archivoPhp) {
                    $posiblesNombres[] = basename($archivoPhp);
                }
            }
            $posiblesNombres = array_unique($posiblesNombres);

            foreach ($posiblesNombres as $nombre) {
                $rutaCompleta = $rutaPlugin . DIRECTORY_SEPARATOR . $nombre;
                if (file_exists($rutaCompleta)) {
                    $datosCabecera = $this->parsearCabeceraPlugin($rutaCompleta);
                    if (!empty($datosCabecera['nombre'])) {
                        $archivoPrincipal = $rutaCompleta;
                        break;
                    }
                }
            }

            if ($archivoPrincipal) {
                $datosPlugin = $this->parsearCabeceraPlugin($archivoPrincipal);
                if (!empty($datosPlugin['nombre'])) {
                    $datosPlugin['slug'] = $slug;
                    $datosPlugin['archivoPrincipal'] = basename($archivoPrincipal);
                    $plugins[$slug] = $datosPlugin;
                }
            }
        }

        return $plugins;
    }

    private function parsearCabeceraPlugin(string $rutaArchivoPHP): array
    {
        $contenido = file_get_contents($rutaArchivoPHP, false, null, 0, 8192);
        $cabeceras = [
            'nombre'   => 'Plugin Name',
            'uri'    => 'Plugin URI',
            'descripcion' => 'Description',
            'version'  => 'Version',
            'autor'   => 'Author',
            'uriAutor'  => 'Author URI',
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

    public function activarPlugin(string $slug): bool
    {
        $opcionService = new OpcionService();
        $pluginsDisponibles = $this->obtenerPluginsDisponibles();

        if (!isset($pluginsDisponibles[$slug])) {
            throw new \Exception("El plugin '{$slug}' no es un plugin válido.");
        }

        $pluginsActivos = $opcionService->obtenerOpcion('active_plugins', []);

        if (in_array($slug, $pluginsActivos)) {
            return true; // Ya estaba activo, la operación se considera exitosa.
        }

        $pluginsActivos[] = $slug;
        $guardado = $opcionService->guardarOpcion('active_plugins', $pluginsActivos);

        if ($guardado) {
            PluginRegistry::$activePlugins = $pluginsActivos;
            forzarReinicioServidor();
        }

        return $guardado;
    }

    public function desactivarPlugin(string $slug): bool
    {
        $opcionService = new OpcionService();
        $pluginsActivos = $opcionService->obtenerOpcion('active_plugins', []);

        $key = array_search($slug, $pluginsActivos);

        if ($key === false) {
            return true; // No estaba activo, la operación se considera exitosa.
        }

        unset($pluginsActivos[$key]);
        $nuevosPluginsActivos = array_values($pluginsActivos);
        $guardado = $opcionService->guardarOpcion('active_plugins', $nuevosPluginsActivos);
        forzarReinicioServidor();
        if ($guardado) {
            PluginRegistry::$activePlugins = $nuevosPluginsActivos;
            forzarReinicioServidor();
        }

        return $guardado;
    }
}
