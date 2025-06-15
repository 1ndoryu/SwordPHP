<?php

namespace App\controller;

use App\service\PaginaService;
use support\Request;
use support\Response;
use Webman\Exception\NotFoundException; // Importar la excepción

class PaginaPublicaController
{
    private PaginaService $paginaService;

    public function __construct(PaginaService $paginaService)
    {
        $this->paginaService = $paginaService;
    }

    /**
     * Muestra una página pública basada en su slug.
     */
    public function mostrar(Request $request, string $slug): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPublicadaPorSlug($slug);

            // Si se encuentra la página, se renderiza la vista correspondiente.
            // La vista a utilizar puede estar definida en los metadatos de la página.
            $plantilla = $pagina->obtenerMeta('plantilla_vista') ?? 'pagina';

            return view($plantilla, ['pagina' => $pagina]);

        } catch (NotFoundException $e) {
            // Si la página no se encuentra, se devuelve una respuesta 404.
            return response("<h1>404 | Página No Encontrada</h1>", 404);
        }
    }
}