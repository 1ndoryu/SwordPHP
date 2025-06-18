<?php

namespace App\controller;

use App\service\SwordQuery;
use support\Request;
use support\Response;

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
    public function mostrar(Request $request, string $slug = null, string $año = null, string $mes = null, string $dia = null, int $id = null): Response
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

        // En el futuro, los parámetros de fecha ($año, $mes, $dia) se podrían usar
        // para desambiguar slugs o para validación adicional.

        // 1. Crear la consulta principal para la página/entrada solicitada.
        $swordConsultaPrincipal = new SwordQuery($argumentos);

        // 2. Si la consulta no encuentra ninguna entrada, es un 404.
        if ($swordConsultaPrincipal->totalEntradas === 0) {
            return response("<h1>404 | Página No Encontrada</h1>", 404);
        }

        // 3. Accedemos a la primera entrada solo para determinar la plantilla a usar.
        $entradaParaPlantilla = $swordConsultaPrincipal->entradas->first();

        // Plantilla por defecto.
        $plantillaAUsar = 'pagina';
        $nombreArchivoPlantilla = $entradaParaPlantilla->obtenerMeta('_plantilla_pagina');

        if (!empty($nombreArchivoPlantilla)) {
            $rutaCompletaPlantilla = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . config('theme.active_theme') . DIRECTORY_SEPARATOR . $nombreArchivoPlantilla;
            if (is_file($rutaCompletaPlantilla)) {
                $plantillaAUsar = pathinfo($nombreArchivoPlantilla, PATHINFO_FILENAME);
            }
        }

        // 4. Renderizar la vista. La plantilla ahora es responsable de ejecutar el loop.
        return view($plantillaAUsar, []);
    }
}
