<?php

namespace App\controller;

use App\service\OpcionService;
use App\service\PluginService;
use support\Request;
use support\Response;
use support\Log;

/**
 * Controlador para la gestión de plugins desde el panel de administración.
 */
class PluginController
{
    private PluginService $pluginService;
    private OpcionService $opcionService;

    /**
     * Inyecta los servicios necesarios en el controlador.
     *
     * @param PluginService $pluginService
     * @param OpcionService $opcionService
     */
    public function __construct(PluginService $pluginService, OpcionService $opcionService)
    {
        $this->pluginService = $pluginService;
        $this->opcionService = $opcionService;
    }

    /**
     * Muestra la página de gestión de plugins.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Obtenemos todos los plugins disponibles usando el servicio.
        $pluginsDisponibles = $this->pluginService->obtenerPluginsDisponibles();

        // Obtenemos los slugs de los plugins activos desde la base de datos.
        // Usamos un array vacío como valor por defecto.
        $pluginsActivos = $this->opcionService->getOption('active_plugins', []);

        // Obtenemos posibles mensajes flash de la sesión.
        $mensajeExito = $request->session()->pull('success');
        $mensajeError = $request->session()->pull('error');

        // La vista 'admin/plugins/index' se creará en un paso posterior.
        return view('admin/plugins/index', [
            'tituloPagina' => 'Gestión de Plugins',
            'plugins' => $pluginsDisponibles,
            'pluginsActivos' => $pluginsActivos,
            'mensajeExito' => $mensajeExito,
            'mensajeError' => $mensajeError,
        ]);
    }

    /**
     * Procesa la solicitud para activar un plugin.
     *
     * @param Request $request
     * @param string $slug El slug del plugin a activar.
     * @return Response
     */
    public function activar(Request $request, string $slug): Response
    {
        try {
            $this->pluginService->activarPlugin($slug);
            $request->session()->set('success', "Plugin '{$slug}' activado correctamente.");
        } catch (\Throwable $e) {
            Log::error('Error al activar el plugin: ' . $e->getMessage());
            $request->session()->set('error', 'Error al activar el plugin: ' . $e->getMessage());
        }

        return redirect('/panel/plugins');
    }

    /**
     * Procesa la solicitud para desactivar un plugin.
     *
     * @param Request $request
     * @param string $slug El slug del plugin a desactivar.
     * @return Response
     */
    public function desactivar(Request $request, string $slug): Response
    {
        try {
            $this->pluginService->desactivarPlugin($slug);
            $request->session()->set('success', "Plugin '{$slug}' desactivado correctamente.");
        } catch (\Throwable $e) {
            Log::error('Error al desactivar el plugin: ' . $e->getMessage());
            $request->session()->set('error', 'Error al desactivar el plugin: ' . $e->getMessage());
        }

        return redirect('/panel/plugins');
    }
}