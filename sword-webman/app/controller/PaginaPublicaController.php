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
        // El servicio ya lanza NotFoundException si no encuentra la página publicada,
        // así que no necesitamos un try-catch. El manejador de excepciones
        // global se encargará de la respuesta 404.
        $pagina = $this->paginaService->obtenerPaginaPublicadaPorSlug($slug);

        // Por ahora, renderizamos una vista genérica.
        // Más adelante, esto interactuará con el sistema de temas.
        return view('frontend.pagina', [
            'pagina' => $pagina,
            'titulo' => $pagina->titulo
        ]);
    }
}
