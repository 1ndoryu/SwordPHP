<?php

namespace app\controller;

use support\Request;

class IndexController
{
    public function index(Request $request)
    {
        $tiempoInicio = microtime(true);

        // Futura lógica para construir la página irá aquí.

        $tiempoFin = microtime(true);
        $tiempoCarga = ($tiempoFin - $tiempoInicio) * 1000;

        $datosContenido = [
            'tiempoCarga' => number_format($tiempoCarga, 2)
        ];

        $datosLayout = [
            'titulo' => 'Página de Inicio'
        ];

        return renderConLayout('index/index', $datosContenido, $datosLayout);
    }
}
