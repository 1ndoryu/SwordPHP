<?php

namespace App\controller;

use App\service\TemaService;
use support\Request;
use support\Response;

/**
 * Controlador para la gestión de temas desde el panel de administración.
 */
class TemaController
{
    private TemaService $temaService;

    /**
     * Inyecta el servicio de temas en el controlador.
     *
     * @param TemaService $temaService
     */
    public function __construct(TemaService $temaService)
    {
        $this->temaService = $temaService;
    }

    /**
     * Muestra la página de gestión de temas.
     *
     * Obtiene todos los temas disponibles, identifica el que está activo
     * y renderiza la vista con toda la información.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Obtenemos todos los temas disponibles usando el servicio.
        $temasDisponibles = $this->temaService->obtenerTemasDisponibles();

        // Obtenemos el slug del tema activo desde la configuración.
        $temaActivoSlug = config('theme.active_theme', '');

        // Obtenemos posibles mensajes flash de la sesión.
        $mensajeExito = $request->session()->pull('success');
        $mensajeError = $request->session()->pull('error');

        return view('admin/temas/index', [
            'tituloPagina' => 'Gestión de Temas',
            'temas' => $temasDisponibles,
            'temaActivo' => $temaActivoSlug,
            'mensajeExito' => $mensajeExito,
            'mensajeError' => $mensajeError,
        ]);
    }
}
