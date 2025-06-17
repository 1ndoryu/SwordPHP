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

    /**
     * Activa un tema específico escribiendo su slug en el archivo de configuración.
     *
     * @param string $slug El identificador (directorio) del tema a activar.
     * @return bool Devuelve true si la operación fue exitosa, false en caso contrario.
     * @throws \Exception Si el tema no existe o si hay problemas de permisos de escritura.
     */
    public function activarTema(string $slug): bool
    {
        // 1. Validar que el tema a activar realmente existe.
        $temasDisponibles = $this->obtenerTemasDisponibles();
        if (!isset($temasDisponibles[$slug])) {
            throw new \Exception("El tema '{$slug}' no es un tema válido o no se pudo encontrar.");
        }

        // 2. Modificar el archivo de configuración de forma segura.
        $rutaConfig = config_path('theme.php');

        if (!is_writable($rutaConfig)) {
            // Es crucial verificar los permisos para evitar errores fatales.
            throw new \Exception("Error de permisos: el archivo '{$rutaConfig}' no tiene permisos de escritura.");
        }

        $contenidoConfig = file_get_contents($rutaConfig);

        // 3. Usar una expresión regular para reemplazar únicamente el valor de 'active_theme'.
        // Esto es robusto contra diferentes espaciados y tipos de comillas (' o ").
        $nuevoContenido = preg_replace(
            "/('active_theme'\\s*=>\\s*)['\"].*?['\"]/",
            "$1'{$slug}'",
            $contenidoConfig,
            1, // Realizar solo un reemplazo
            $reemplazos
        );

        // 4. Verificar que el reemplazo se realizó correctamente.
        if ($reemplazos === 0) {
            throw new \Exception("No se pudo encontrar la clave 'active_theme' en el archivo de configuración.");
        }

        // 5. Escribir el nuevo contenido de vuelta en el archivo.
        return file_put_contents($rutaConfig, $nuevoContenido) !== false;
    }
}
