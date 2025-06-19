<?php

namespace App\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use App\service\PluginRegistry;
use App\service\PluginService;
use support\Log;

class IncludeLoadedPluginsMiddleware implements MiddlewareInterface
{
    private static bool $pluginsIncluded = false;

    public function process(Request $request, callable $handler): Response
    {
        // Si ya se incluyeron o si la lista pre-cargada está vacía, continuar.
        if (self::$pluginsIncluded || empty(PluginRegistry::$activePlugins)) {
            return $handler($request);
        }

        try {
            // Nota: aquí NO se accede a OpcionService. Solo al registro.
            $pluginService = container(PluginService::class);
            $pluginsDisponibles = $pluginService->obtenerPluginsDisponibles();

            foreach (PluginRegistry::$activePlugins as $slug) {
                if (isset($pluginsDisponibles[$slug])) {
                    $archivoPrincipal = $pluginsDisponibles[$slug]['archivoPrincipal'] ?? null;
                    if ($archivoPrincipal) {
                        $rutaCompleta = SWORD_PLUGINS_PATH . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . $archivoPrincipal;
                        if (file_exists($rutaCompleta)) {
                            (function ($file) {
                                require_once $file;
                            })($rutaCompleta);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error al INCLUIR plugins pre-cargados en middleware: " . $e->getMessage());
        }

        self::$pluginsIncluded = true;
        return $handler($request);
    }
}
