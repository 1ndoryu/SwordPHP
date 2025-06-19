<?php

namespace App\controller;

use App\service\SwordQuery;
use support\Request;
use support\Response;
use App\service\OpcionService;

class PaginaPublicaController
{
    /**
     * Muestra una página pública basada en los parámetros de la URL (slug, id, etc.).
     * Este método es lo suficientemente flexible para funcionar con diferentes
     * estructuras de enlaces permanentes.
     *
     * @param Request $request
     * @param string|null $slug El slug de la entrada.
     * @param string|null $año El año de la entrada.
     * @param string|null $mes El mes de la entrada.
     * @param string|null $dia El día de la entrada.
     * @param int|null $id El ID de la entrada.
     * @return Response
     */
    // swordCore/app/controller/PaginaPublicaController.php

    public function mostrar(Request $request, ?string $slug = null, ?string $año = null, ?string $mes = null, ?string $dia = null, ?int $id = null): Response
    {
        global $swordConsultaPrincipal;

        // Construimos los argumentos de la consulta. Por defecto, solo contenido publicado.
        $argumentos = ['post_status' => 'publicado'];

        // Determinamos cómo buscar la entrada, dando prioridad al ID si está presente.
        if ($id) {
            $argumentos['p'] = $id; // Buscar por ID
        } elseif ($slug) {
            $argumentos['name'] = $slug; // Buscar por slug
        } else {
            // Si no hay un identificador (slug o ID), la ruta no es válida.
            return response("<h1>404 | Página No Encontrada</h1><p>No se pudo determinar el contenido a mostrar desde la URL.</p>", 404);
        }

        // 1. Crear la consulta principal para la página/entrada solicitada.
        $swordConsultaPrincipal = new SwordQuery($argumentos);

        // 2. Si la consulta no encuentra ninguna entrada, es un 404.
        if ($swordConsultaPrincipal->totalEntradas === 0) {
            return response("<h1>404 | Página No Encontrada</h1>", 404);
        }

        // 3. Accedemos a la primera entrada para determinar la plantilla a usar.
        $entradaParaPlantilla = $swordConsultaPrincipal->entradas->first();
        $tipoContenido = $entradaParaPlantilla->tipocontenido;
        $nombreArchivoPlantilla = null;

        // --- Lógica de Selección de Plantilla con Prioridad ---

        // Prioridad 1: Ajuste del Tipo de Contenido (para CPTs)
        if ($tipoContenido !== 'pagina') {
            $opcionService = new OpcionService();
            $ajustesCPT = $opcionService->obtenerOpcion("ajustes_cpt_{$tipoContenido}");
            if (!empty($ajustesCPT['plantilla_single'])) {
                $nombreArchivoPlantilla = $ajustesCPT['plantilla_single'];
            }
        }

        // Prioridad 2: Metadato de la página individual (principalmente para 'paginas')
        // Se aplica si no se encontró una plantilla en el paso anterior.
        if (is_null($nombreArchivoPlantilla)) {
            $metaPlantilla = $entradaParaPlantilla->obtenerMeta('_plantilla_pagina');
            if (!empty($metaPlantilla)) {
                $nombreArchivoPlantilla = $metaPlantilla;
            }
        }

        // 4. Determinar la plantilla final a usar.
        // Por ahora, el fallback por defecto si no se encuentra nada es 'pagina'.
        $plantillaAUsar = 'pagina';

        if (!empty($nombreArchivoPlantilla)) {
            // CORRECCIÓN: Usar el TemaService para obtener el tema activo dinámicamente.
            $rutaCompletaPlantilla = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . \App\service\TemaService::getActiveTheme() . DIRECTORY_SEPARATOR . $nombreArchivoPlantilla;
            if (is_file($rutaCompletaPlantilla)) {
                $plantillaAUsar = pathinfo($nombreArchivoPlantilla, PATHINFO_FILENAME);
            }
        }

        // 5. Renderizar la vista. La plantilla ahora es responsable de ejecutar el loop.
        return view($plantillaAUsar, []);
    }
}
