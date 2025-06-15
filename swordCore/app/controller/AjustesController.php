<?php

namespace App\controller;

use App\service\OpcionService;
use App\service\PaginaService;
use support\Request;
use support\Response;

class AjustesController
{
    private PaginaService $paginaService;
    private OpcionService $opcionService;

    public function __construct(PaginaService $paginaService, OpcionService $opcionService)
    {
        $this->paginaService = $paginaService;
        $this->opcionService = $opcionService;
    }

    /**
     * Muestra la página de ajustes generales.
     * Carga las páginas publicadas y la configuración actual de la página de inicio.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $paginasPublicadas = $this->paginaService->obtenerPaginasPublicadas();
        $paginaInicioActualSlug = $this->opcionService->obtenerOpcion('pagina_de_inicio_slug');

        $mensajeExito = $request->session()->pull('mensaje_exito');

        return view('admin/ajustes/index', [
            'paginas' => $paginasPublicadas,
            'paginaInicioActual' => $paginaInicioActualSlug,
            'mensajeExito' => $mensajeExito
        ]);
    }

    /**
     * Guarda el ajuste de la página de inicio.
     *
     * @param Request $request
     * @return Response
     */
    public function guardar(Request $request): Response
    {
        $slugPaginaInicio = $request->post('pagina_inicio');

        $this->opcionService->guardarOpcion('pagina_de_inicio_slug', $slugPaginaInicio);

        $request->session()->set('mensaje_exito', 'Ajustes guardados correctamente.');

        return redirect('/panel/ajustes');
    }
}
