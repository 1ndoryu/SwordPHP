<?php

namespace App\controller;

use App\service\SwordQuery;
use support\Request;
use support\Response;

class PaginaPublicaController
{
    /**
     * Muestra una página pública basada en su slug.
     *
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function mostrar(Request $request, string $slug): Response
    {
        global $swordConsultaPrincipal;

        // 1. Crear la consulta principal para la página/entrada solicitada.
        // Se busca por slug en cualquier tipo de contenido que esté publicado.
        $argumentos = [
            'name' => $slug,
            'post_status' => 'publicado',
        ];
        $swordConsultaPrincipal = new SwordQuery($argumentos);

        // 2. Si la consulta no encuentra ninguna entrada, es un 404.
        if ($swordConsultaPrincipal->totalEntradas === 0) {
            return response("<h1>404 | Página No Encontrada</h1>", 404);
        }

        // 3. Accedemos a la primera entrada solo para determinar la plantilla a usar.
        $entradaParaPlantilla = $swordConsultaPrincipal->entradas->first();
        
        // Plantilla por defecto. Si la entrada es de un tipo de contenido diferente
        // a 'pagina', podríamos tener lógica para buscar plantillas como 'single-{tipoContenido}.php'.
        // Por ahora, usamos 'pagina.php' como fallback general.
        $plantillaAUsar = 'pagina';
        $nombreArchivoPlantilla = $entradaParaPlantilla->obtenerMeta('_plantilla_pagina');

        if (!empty($nombreArchivoPlantilla)) {
            $rutaCompletaPlantilla = SWORD_THEMES_PATH . DIRECTORY_SEPARATOR . config('theme.active_theme') . DIRECTORY_SEPARATOR . $nombreArchivoPlantilla;
            if (is_file($rutaCompletaPlantilla)) {
                $plantillaAUsar = pathinfo($nombreArchivoPlantilla, PATHINFO_FILENAME);
            }
        }
        
        // 4. Renderizar la vista. La plantilla ahora es responsable de ejecutar el loop
        // (`hayEntradas`, `laEntrada`, etc.) usando la variable global $swordConsultaPrincipal.
        return view($plantillaAUsar, []);
    }
}