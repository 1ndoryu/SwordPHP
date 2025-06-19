<?php

namespace App\controller;

use App\service\TemaService;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

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

        // Obtenemos el slug del tema activo desde el TemaService, que ahora es la fuente de verdad.
        $temaActivoSlug = \App\service\TemaService::getActiveTheme();

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
    
    /**
     * Procesa la solicitud para activar un nuevo tema.
     *
     * @param Request $request
     * @param string $slug El slug del tema a activar.
     * @return Response Una redirección a la página de temas.
     */
    public function activar(Request $request, string $slug): Response
    {
        try {
            $this->temaService->activarTema($slug);
            $request->session()->set('success', "Tema '{$slug}' activado. El sistema se recargará para aplicar los cambios.");
        } catch (Throwable $e) {
            Log::error('Error al activar el tema: ' . $e->getMessage());
            $request->session()->set('error', 'Error al activar el tema: ' . $e->getMessage());
        }

        return redirect('/panel/temas');
    }
}
