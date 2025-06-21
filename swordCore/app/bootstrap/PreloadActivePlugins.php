<?php
namespace App\bootstrap;

use Webman\Bootstrap;
use App\service\OpcionService;
use App\service\PluginRegistry;
use support\Log;

class PreloadActivePlugins implements Bootstrap
{
    public static function start($worker)
    {
        // Solo continuar si la instalación está completa.
        if (!file_exists(runtime_path('installed.lock'))) {
            return;
        }

        try {
            // La única acción: consultar la BD y guardar la lista de slugs.
            $opcionService = container(OpcionService::class);
            PluginRegistry::$activePlugins = $opcionService->getOption('active_plugins', []);
        } catch (\Throwable $e) {
            // Si esto falla, es una información crítica.
            Log::error("FALLO CRITICO EN BOOTSTRAP: No se pudo pre-cargar la lista de plugins desde la BD. Error: " . $e->getMessage());
        }
    }
}