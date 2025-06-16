<?php

namespace App\service;

/**
 * Servicio para gestionar los temas.
 *
 * Se encarga de escanear, parsear y gestionar los temas disponibles
 * en la instalación de SwordPHP.
 */
class TemaService
{
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
            // Devuelve un array vacío si el directorio de temas no existe.
            return [];
        }

        // Escanea el directorio de temas.
        $directorios = array_filter(scandir($directorioTemas), fn($dir) => !in_array($dir, ['.', '..']));

        foreach ($directorios as $slug) {
            $rutaTema = $directorioTemas . DIRECTORY_SEPARATOR . $slug;
            $rutaCss = $rutaTema . DIRECTORY_SEPARATOR . 'style.css';

            if (is_dir($rutaTema) && file_exists($rutaCss)) {
                $datosTema = $this->parsearCabeceraTema($rutaCss);
                if (!empty($datosTema['nombre'])) {
                    $datosTema['slug'] = $slug;
                    // Añadimos una ruta a una posible captura de pantalla para la vista.
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
            'nombre'      => 'Theme Name',
            'uri'         => 'Theme URI',
            'descripcion' => 'Description',
            'autor'       => 'Author',
            'uriAutor'    => 'Author URI',
            'version'     => 'Version',
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
}
