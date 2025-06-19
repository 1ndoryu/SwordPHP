<?php

namespace support\view;

use App\service\TemaService;
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
     * Renderiza una plantilla PHP nativa, determinando dinámicamente la ruta del tema.
     *
     * @param string $template La ruta de la plantilla (ej: 'admin/paginas/index').
     * @param array $vars Las variables que se pasarán a la plantilla.
     * @param string|null $app No se utiliza en esta implementación.
     * @return string
     */
    public static function render(string $template, array $vars, ?string $app = null): string
    {
        // Caché estático para las rutas de las vistas para evitar recalcularlas en cada render.
        static $viewPaths = null;

        // Si las rutas no han sido calculadas en esta petición, las determinamos.
        if ($viewPaths === null) {
            // Usamos el nuevo método estático para obtener el tema activo.
            $activeTheme = TemaService::getActiveTheme();

            // Construimos el array de rutas, dando prioridad al tema activo.
            $viewPaths = [
                SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $activeTheme,
                app_path() . DIRECTORY_SEPARATOR . 'view',
            ];
        }

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
        extract($vars);

        // Inicia el búfer de salida para capturar el HTML.
        ob_start();
        include $viewFile;
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