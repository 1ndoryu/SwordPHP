<?php

namespace app\controller;

use support\Request;
use support\Db;

class IndexController
{
    public function index(Request $request)
    {
        $tiempoInicio = microtime(true);
        $estadoConexion = 'No se pudo verificar la conexión.';

        try {
            // Realizamos una consulta simple para verificar la conexión.
            Db::select('SELECT 1');
            $estadoConexion = 'Conexión a la base de datos exitosa.';
        } catch (\Exception $e) {
            // En caso de error, es una buena práctica registrar el mensaje para depuración.
            Log::error('Error de conexión a BD: ' . $e->getMessage());
            $estadoConexion = 'Error al conectar con la base de datos.';
        }

        // Calculamos el tiempo de carga total de la petición en milisegundos.
        $tiempoCarga = (microtime(true) - $tiempoInicio) * 1000;

        // Pasamos las variables a la vista.
        return view('index.inicio', [
            'estadoConexion' => $estadoConexion,
            'tiempoCarga'    => number_format($tiempoCarga, 2)
        ]);
    }
    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    public function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }
}
