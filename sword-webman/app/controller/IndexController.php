<?php

namespace app\controller;

use support\Request;
use App\service\DatabaseService;

class IndexController
{
    public function index(Request $request)
    {
        $databaseService = new DatabaseService();
        $dbStatus = $databaseService->verificarConexion();

        if ($dbStatus === true) {
            $dbStatus = 'Conectado';
        }

        return view('index.inicio', [
            'estadoConexion' => $dbStatus
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
