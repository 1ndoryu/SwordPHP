<?php

namespace App\controller;

use App\service\UsuarioService;
use Webman\Http\Request;
use Webman\Http\Response;
use support\Log;
use support\exception\BusinessException;
use Throwable;

class AuthController
{
    protected UsuarioService $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    public function mostrarFormularioRegistro(Request $request): Response
    {
        return view('auth.registro', [
            'titulo' => 'Crear una cuenta',
            'exito' => session()->pull('exito'),
            'error' => session()->pull('error')
        ]);
    }

    public function procesarRegistro(Request $request): Response
    {
        try {
            $datos = $request->post();
            $this->usuarioService->crearUsuario($datos);

            session()->set('exito', '¡Cuenta creada correctamente! Ya puedes iniciar sesión.');
            return redirect('/login');
        } catch (BusinessException $e) {
            session()->set('error', $e->getMessage());
            session()->set('_old_input', $request->post());
            return redirect('/registro');
        } catch (Throwable $e) {
            Log::error('Error en el registro de usuario: ' . $e->getMessage());
            session()->set('error', 'Ocurrió un error inesperado durante el registro.');
            session()->set('_old_input', $request->post());
            return redirect('/registro');
        }
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
            $session = $request->session();
            $session->set('usuarioId', $usuario->id);

            Log::channel('session_debug')->info('AuthController: Login exitoso. Guardando usuarioId en sesión.', [
                'session_id' => $session->getId(),
                'usuarioId_guardado' => $usuario->id,
                'session_data_despues' => $session->all()
            ]);

            // Usamos el helper estándar para la redirección.
            return redirect('/panel');
        }

        Log::channel('session_debug')->warning('AuthController: Login fallido.', [
            'identificador' => $identificador
        ]);
        session()->set('error', 'Las credenciales proporcionadas son incorrectas.');
        return redirect('/login');
    }

    public function procesarLogout(Request $request): Response
    {
        $session = $request->session();
        Log::channel('session_debug')->info('AuthController: Procesando logout.', [
            'session_id' => $session->getId(),
            'session_data_antes' => $session->all()
        ]);
        $session->flush();
        session()->set('exito', 'Has cerrado sesión correctamente.');
        return redirect('/login');
    }
}
