<?php

namespace App\controller;

use App\service\PaginaService;
use support\Request;
use Webman\Http\Response;

class PaginaPublicaController
{
    private PaginaService $paginaService;

    public function __construct(PaginaService $paginaService)
    {
        $this->paginaService = $paginaService;
    }

    /**
     * Muestra una página pública basada en su slug.
     *
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function mostrar(Request $request, string $slug): Response
    {
        // LÍNEA DE DEPURACIÓN: Muestra las rutas de vista y detiene la ejecución.
        //dd(config('view.options.view_path'));

        // El resto de tu código...
        $pagina = $this->paginaService->obtenerPaginaPublicadaPorSlug($slug);

        return view('pagina', [
            'pagina' => $pagina,
            'titulo' => $pagina->titulo
        ]);
    }
}
