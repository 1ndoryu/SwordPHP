<?php

namespace App\middleware;

use App\service\TemaService;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use support\Log;

/**
 * Middleware para incluir el archivo functions.php del tema activo.
 * Se asegura de que las definiciones y hooks del tema estén disponibles
 * en toda la aplicación.
 */
class IncludeActiveThemeMiddleware implements MiddlewareInterface
{
    /** @var bool Para asegurar que el archivo se incluye solo una vez por petición. */
    private static bool $themeFunctionsIncluded = false;

    public function process(Request $request, callable $handler): Response
    {
        // Si ya se incluyó en esta petición, no hacer nada.
        if (self::$themeFunctionsIncluded) {
            return $handler($request);
        }

        try {
            // No intentar cargar nada si el CMS no está habilitado o aún no se ha instalado.
            if (!env('CMS_ENABLED', true) || !file_exists(runtime_path('installed.lock'))) {
                return $handler($request);
            }

            $activeTheme = TemaService::getActiveTheme();

            if ($activeTheme) {
                $functionsPath = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . $activeTheme . DIRECTORY_SEPARATOR . 'functions.php';

                if (file_exists($functionsPath)) {
                    // Usamos una IIFE (función anónima autoejecutable) para aislar el scope del archivo incluido
                    // y evitar que variables definidas en él se filtren al scope del middleware.
                    (function ($file) {
                        require_once $file;
                    })($functionsPath);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error crítico al incluir functions.php del tema activo: " . $e->getMessage());
        }

        self::$themeFunctionsIncluded = true;
        return $handler($request);
    }
}
