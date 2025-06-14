<?php

namespace App\controller;

use App\service\UsuarioService;
use Webman\Http\Request;
use Webman\Http\Response;
use support\Log;

class AuthController
{
    protected UsuarioService $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    public function mostrarFormularioRegistro(Request $request): Response
    {
        return view('auth.registro', ['titulo' => 'Crear una cuenta']);
    }

    public function procesarRegistro(Request $request): Response
    {
        $datos = $request->post();
        $usuario = $this->usuarioService->crearUsuario($datos);

        if ($usuario) {
            session()->set('exito', '¡Cuenta creada correctamente! Ya puedes iniciar sesión.');
            return redirect('/login');
        }

        session()->set('error', 'No se pudo crear la cuenta. El email o nombre de usuario puede que ya esté en uso.');
        return redirect('/registro');
    }

    public function mostrarFormularioLogin(Request $request): Response
    {
        return view('auth.login', [
            'titulo' => 'Iniciar Sesión',
            'exito' => session()->pull('exito'),
            'error' => session()->pull('error')
        ]);
    }

    public function procesarLogin(Request $request): Response
    {
        $identificador = $request->post('identificador');
        $clave = $request->post('clave');
        $usuario = $this->usuarioService->autenticarUsuario($identificador, $clave);

        if ($usuario) {
            $request->session()->set('usuarioId', $usuario->id);
            Log::channel('default')->info('[AuthController] -> Login exitoso. Usuario ID: ' . $usuario->id . '. Redirigiendo a /panel.');

            // CORRECCIÓN: Construimos la respuesta de redirección manualmente.
            $host = $request->header('host');
            $url = 'http://' . $host . '/panel';
            return new Response(302, ['Location' => $url]);
        }

        Log::channel('default')->warning('[AuthController] -> Login fallido para el identificador: ' . $identificador);
        // Para esta redirección de fallo, el helper debería funcionar, pero lo cambiaremos por consistencia si es necesario.
        return redirect('/login');
    }

    public function procesarLogout(Request $request): Response
    {
        $request->session()->flush();
        session()->set('exito', 'Has cerrado sesión correctamente.');
        return redirect('/login');
    }
}
