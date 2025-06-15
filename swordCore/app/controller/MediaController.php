<?php

namespace App\controller;

use support\Request;
use Webman\Http\Response;

class MediaController
{
    /**
     * Muestra la página principal de la biblioteca de medios.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Más adelante, aquí obtendremos los archivos de la base de datos.
        // Por ahora, simplemente renderizamos la vista.
        return view('admin/media/index', [
            'tituloPagina' => 'Biblioteca de Medios'
        ]);
    }
}