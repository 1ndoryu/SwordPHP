<?php

namespace support\view;

use Jenssegers\Blade\Blade as BladeView;
use Webman\View;
use function config;

/**
 * Nuestro manejador de Blade personalizado que SÍ respeta el array de rutas de vista.
 * Esto permite el sistema de temas con fallback al core.
 */
class SwordBlade implements View
{
    /**
     * Mantenemos una única instancia de Blade para no recrearla en cada petición.
     * @var BladeView|null
     */
    private static ?BladeView $_instance = null;

    /**
     * Asigna variables a la vista.
     * Esta función no la necesitamos para nuestro problema, pero es requerida por la interfaz.
     */
    public static function assign(string|array $name, mixed $value = null): void
    {
        // No es necesario implementar esto para la solución.
    }

    /**
     * Renderiza la plantilla.
     */
    public static function render(string $template, array $vars, ?string $app = null, ?string $plugin = null): string
    {
        // Si todavía no hemos creado la instancia de Blade, la creamos.
        if (!static::$_instance) {
            // ¡ESTA ES LA LÓGICA CORRECTA!
            // 1. Obtenemos el ARRAY de rutas desde la configuración.
            $viewPaths = config('view.options.view_path');

            // 2. Obtenemos la ruta de la caché.
            $cachePath = config('view.options.cache_path');

            // 3. Creamos la instancia de Blade pasándole el ARRAY de rutas.
            static::$_instance = new BladeView($viewPaths, $cachePath);
        }

        // Renderizamos la vista usando nuestra instancia correctamente configurada.
        return static::$_instance->render($template, $vars);
    }
}
