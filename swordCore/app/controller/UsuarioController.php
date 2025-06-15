<?php

namespace App\controller;

use App\service\UsuarioService;
use support\Request;
use support\Response;

class UsuarioController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Muestra la lista paginada de usuarios.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        
        $usuarios = $this->usuarioService->obtenerUsuariosPaginados();
        return view('admin/usuarios/index', [
            'titulo' => 'Gestión de Usuarios',
            'usuarios' => $usuarios
        ]);
    }
}
