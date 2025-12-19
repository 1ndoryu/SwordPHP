<?php

namespace app\controller;

use app\support\ThemeEngine;
use app\model\Content;
use support\Request;
use support\Response;
use support\Log;

/**
 * Controlador Frontend
 * 
 * Maneja el renderizado de páginas públicas usando el sistema de temas.
 */
class FrontendController
{
    private ThemeEngine $tema;

    public function __construct()
    {
        $this->tema = ThemeEngine::instancia();
    }

    /**
     * Página de inicio
     */
    public function inicio(Request $request): Response
    {
        $paginaInicio = get_option('pagina_inicio', null);

        /* Si hay una página configurada como inicio, mostrarla */
        if ($paginaInicio) {
            $contenido = Content::where('id', $paginaInicio)
                ->where('status', 'published')
                ->first();

            if ($contenido) {
                return $this->renderizarContenido($contenido->toArray(), 'page');
            }
        }

        /* Si no, mostrar el listado de posts (blog) */
        return $this->archivo($request, 'post');
    }

    /**
     * Página o contenido individual por slug
     */
    public function mostrar(Request $request, string $slug): Response
    {
        $contenido = Content::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$contenido) {
            return $this->paginaNoEncontrada();
        }

        return $this->renderizarContenido($contenido->toArray(), $contenido->type);
    }

    /**
     * Archivo/listado de contenidos
     */
    public function archivo(Request $request, string $tipo = 'post'): Response
    {
        $pagina = (int) $request->get('pagina', 1);
        $porPagina = (int) get_option('posts_por_pagina', 10);

        $query = Content::where('type', $tipo)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $totalPaginas = ceil($total / $porPagina);

        $posts = $query->offset(($pagina - 1) * $porPagina)
            ->limit($porPagina)
            ->get()
            ->toArray();

        $plantilla = $this->tema->resolverPlantilla('archive', $tipo);

        if (!$plantilla) {
            Log::error("No se encontró plantilla para archivo de tipo: {$tipo}");
            return $this->paginaNoEncontrada();
        }

        $html = $this->tema->renderizar($plantilla, [
            'posts' => $posts,
            'tipo' => $tipo,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'total' => $total
        ]);

        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }

    /**
     * Blog (alias para archivo de posts)
     */
    public function blog(Request $request): Response
    {
        return $this->archivo($request, 'post');
    }

    /**
     * Renderiza un contenido individual
     */
    private function renderizarContenido(array $contenido, string $tipo): Response
    {
        $plantilla = $this->tema->resolverPlantilla(
            $tipo,
            $contenido['slug'] ?? null,
            $contenido['id'] ?? null
        );

        if (!$plantilla) {
            Log::error("No se encontró plantilla para: {$tipo}/{$contenido['slug']}");
            return $this->paginaNoEncontrada();
        }

        /* Variable global para los template tags */
        $GLOBALS['contenido'] = $contenido;

        $html = $this->tema->renderizar($plantilla, [
            'contenido' => $contenido
        ]);

        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }

    /**
     * Página 404
     */
    private function paginaNoEncontrada(): Response
    {
        $rutaPlantillas = $this->tema->obtenerRutaPlantillas();
        $plantilla404 = $rutaPlantillas . '/404.php';

        if (file_exists($plantilla404)) {
            $html = $this->tema->renderizar($plantilla404, []);
        } else {
            $html = $this->html404Basico();
        }

        return new Response(404, ['Content-Type' => 'text/html'], $html);
    }

    /**
     * HTML básico para 404 si no hay plantilla
     */
    private function html404Basico(): string
    {
        $nombreSitio = htmlspecialchars(get_option('site_name', 'SwordPHP'));

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - {$nombreSitio}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-size: 6rem;
            margin: 0;
            color: #333;
        }
        p {
            font-size: 1.25rem;
            color: #666;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>La página que buscas no existe.</p>
        <a href="/">Volver al inicio</a>
    </div>
</body>
</html>
HTML;
    }
}
