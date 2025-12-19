<?php

namespace app\controller\Admin;

use app\support\ThemeEngine;
use support\Request;
use support\Response;

/**
 * Controlador de Temas para el Admin
 * 
 * Gestionar temas instalados: listar, activar, preview.
 */
class ThemeController
{
    private ThemeEngine $temaEngine;

    public function __construct()
    {
        $this->temaEngine = ThemeEngine::instancia();
    }

    /**
     * Listar todos los temas instalados
     * 
     * Si es petición AJAX devuelve JSON, si es acceso directo sirve el panel React
     */
    public function index(Request $request): Response
    {
        /* Si es petición AJAX/fetch, devolver JSON */
        $acceptHeader = $request->header('accept') ?? '';
        if ($request->isAjax() || str_contains($acceptHeader, 'application/json')) {
            $temas = $this->temaEngine->listarTemas();

            return json([
                'success' => true,
                'data' => [
                    'temas' => $temas,
                    'temaActivo' => $this->temaEngine->obtenerTemaActivo()
                ]
            ]);
        }

        /* Si es acceso directo del navegador, servir el panel React */
        $content = render_view('admin/pages/dashboard');
        return response(render_view('admin/layouts/layout', [
            'title' => 'Temas',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]));
    }

    /**
     * Obtener detalles de un tema específico
     */
    public function show(Request $request, string $slug): Response
    {
        if (!$this->temaEngine->temaExiste($slug)) {
            return json([
                'success' => false,
                'message' => "El tema '{$slug}' no existe."
            ], 404);
        }

        $config = $this->temaEngine->cargarConfiguracion($slug);

        return json([
            'success' => true,
            'data' => [
                'slug' => $slug,
                'nombre' => $config['name'] ?? $slug,
                'version' => $config['version'] ?? '1.0.0',
                'autor' => $config['author'] ?? 'Desconocido',
                'descripcion' => $config['description'] ?? '',
                'screenshot' => $config['screenshot'] ?? null,
                'modo' => $config['mode'] ?? 'php',
                'activo' => $slug === $this->temaEngine->obtenerTemaActivo(),
                'soporta' => $config['supports'] ?? []
            ]
        ]);
    }

    /**
     * Activar un tema
     */
    public function activate(Request $request, string $slug): Response
    {
        if (!$this->temaEngine->temaExiste($slug)) {
            return json([
                'success' => false,
                'message' => "El tema '{$slug}' no existe."
            ], 404);
        }

        $resultado = $this->temaEngine->activarTema($slug);

        if ($resultado) {
            return json([
                'success' => true,
                'message' => "Tema '{$slug}' activado correctamente.",
                'data' => [
                    'temaActivo' => $slug
                ]
            ]);
        }

        return json([
            'success' => false,
            'message' => "Error al activar el tema '{$slug}'."
        ], 500);
    }

    /**
     * Obtener configuración del tema activo
     */
    public function active(Request $request): Response
    {
        $temaActivo = $this->temaEngine->obtenerTemaActivo();
        $config = $this->temaEngine->obtenerConfiguracion();

        return json([
            'success' => true,
            'data' => [
                'slug' => $temaActivo,
                'configuracion' => $config
            ]
        ]);
    }
}
