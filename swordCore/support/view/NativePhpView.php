<?php

namespace support\view;

use Webman\View;
use function config;

/**
 * Un renderizador de vistas simple para plantillas PHP nativas.
 * Emula el comportamiento de WordPress al permitir incluir archivos .php
 * que mezclan HTML y lógica PHP.
 */
class NativePhpView implements View
{
    /**
     * Renderiza una plantilla PHP nativa.
     *
     * @param string $template La ruta de la plantilla (ej: 'admin/paginas/index').
     * @param array $vars Las variables que se pasarán a la plantilla.
     * @param string|null $app No se utiliza en esta implementación.
     * @return string
     */
    public static function render(string $template, array $vars, ?string $app = null): string
    {
        // Obtiene el array de rutas de vista desde la configuración.
        // Esto permite buscar primero en el tema y luego en el core.
        $viewPaths = config('view.options.view_path', []);

        // Convierte la notación de puntos (ej: 'admin.paginas.index') a slashes.
        $templatePath = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';

        // Busca el archivo de la plantilla en las rutas definidas.
        $viewFile = null;
        foreach ($viewPaths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $templatePath)) {
                $viewFile = $path . DIRECTORY_SEPARATOR . $templatePath;
                break;
            }
        }

        if (!$viewFile) {
            // Si no se encuentra la plantilla, lanza una excepción.
            throw new \RuntimeException("Vista no encontrada: {$template}");
        }

        // `extract` convierte las claves del array asociativo en variables.
        // Ejemplo: ['titulo' => 'Mi Título'] se convierte en la variable $titulo.
        extract($vars);

        // Inicia el búfer de salida. Esto captura todo lo que se "imprima"
        // (con echo, <?=, etc.) en lugar de enviarlo directamente al navegador.
        ob_start();

        // Incluye el archivo de la plantilla. Las variables extraídas ($vars)
        // estarán disponibles dentro de este archivo.
        include $viewFile;

        // Devuelve el contenido del búfer capturado y lo limpia.
        return ob_get_clean();
    }

    /**
     * Este método es requerido por la interfaz View, pero no lo necesitamos.
     */
    public static function assign(string|array $name, mixed $value = null): void
    {
        // No aplicable para este renderizador simple.
    }
}
