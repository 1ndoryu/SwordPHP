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

            // Plantilla por defecto.
            $plantillaAUsar = 'pagina';

            // Obtener el nombre del archivo de la plantilla desde los metadatos.
            $nombreArchivoPlantilla = $pagina->obtenerMeta('_plantilla_pagina');

            if (!empty($nombreArchivoPlantilla)) {
                // Construir la ruta completa al archivo de plantilla dentro del tema activo.
                $rutaCompletaPlantilla = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . config('theme.active_theme') . DIRECTORY_SEPARATOR . $nombreArchivoPlantilla;

                // ¡Importante! Verificar que el archivo de plantilla realmente existe antes de usarlo.
                if (is_file($rutaCompletaPlantilla)) {
                    // El nombre de la vista es el nombre del archivo sin la extensión .php
                    $plantillaAUsar = pathinfo($nombreArchivoPlantilla, PATHINFO_FILENAME);
                }
            }

            // Renderizar la vista, ya sea la personalizada o la por defecto.
            return view($plantillaAUsar, ['pagina' => $pagina]);
        } catch (NotFoundException $e) {
            // Si la página no se encuentra, se devuelve una respuesta 404.
            return response("<h1>404 | Página No Encontrada</h1>", 404);
        }
    }
}
