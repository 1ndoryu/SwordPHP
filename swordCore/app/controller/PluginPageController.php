<?php

namespace App\controller;

use App\service\PluginPageService;
use support\Request;
use support\Response;

class PluginPageController
{
    /**
     * Muestra una página de administración registrada por un plugin.
     *
     * @param Request $request
     * @param string $slug El slug de la página del plugin a mostrar.
     * @return Response
     */
    public function mostrar(Request $request, string $slug): Response
    {
        $pluginPageService = PluginPageService::getInstancia();
        $paginaConfig = $pluginPageService->obtener($slug);

        if (!$paginaConfig) {
            // Si no se encuentra la página, mostramos un error dentro del layout del panel.
            $contenidoError = '<div class="alerta alerta-error">Error 404: La página del plugin solicitada no está registrada o no se encontró.</div>';
            return view('admin.plugin-pagina-wrapper', [
                'tituloPagina' => 'Página no encontrada',
                'contenidoPaginaPlugin' => $contenidoError,
            ]);
        }

        // Ejecutamos el callback del plugin para obtener el contenido HTML.
        $contenidoPaginaPlugin = call_user_func($paginaConfig['callback']);

        // Renderizamos una vista "envoltorio" que proporciona el layout del panel
        // y le pasamos el título y el contenido generado por el plugin.
        return view('admin.plugin-pagina-wrapper', [
            'tituloPagina' => $paginaConfig['page_title'],
            'contenidoPaginaPlugin' => $contenidoPaginaPlugin,
        ]);
    }
}
