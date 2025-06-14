<?php

namespace App\controller;

use support\Request;
use Webman\Http\Response;

class AdminController
{
/**
     * Muestra la página principal del panel de administración (dashboard).
     *
     * @param Request $request
     * @return Response
     */
    public function inicio(Request $request): Response
    {
        // --- INICIO: TEST DE ASSETS ---
        // Encolamos todos los archivos .css del directorio /public/css/panel/
        assetService()->encolarDirectorio('/css/panel', 'css');

        // Encolamos todos los archivos .js del directorio /public/js/panel/
        assetService()->encolarDirectorio('/js/panel', 'js');
        // --- FIN: TEST DE ASSETS ---

        // Más adelante, aquí obtendremos datos del usuario, estadísticas, etc.
        // Por ahora, simplemente renderizamos la vista del dashboard.
        return view('admin.inicio', [
            'titulo' => 'Panel de Administración'
        ]);
    }
}
