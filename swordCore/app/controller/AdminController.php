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



        // Más adelante, aquí obtendremos datos del usuario, estadísticas, etc.
        // Por ahora, simplemente renderizamos la vista del dashboard.
        return view('admin.inicio', [
            'titulo' => 'Panel de Administración'
        ]);
    }
}
