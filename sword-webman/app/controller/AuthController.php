<?php

namespace App\controller;

use App\service\UsuarioService;
use Webman\Http\Request;
use Webman\Http\Response;

class AuthController
{
    /**
     * @var UsuarioService
     */
    protected UsuarioService $usuarioService;

    /**
     * Inyectamos nuestro servicio en el controlador.
     */
    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    /**
     * Muestra el formulario de registro.
     *
     * @param Request $request
     * @return Response
     */
    public function mostrarFormularioRegistro(Request $request): Response
    {
        return view('auth.registro', ['titulo' => 'Crear una cuenta']);
    }

    /**
     * Procesa la solicitud de creación de una nueva cuenta.
     *
     * @param Request $request
     * @return Response
     */
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

    /**
     * Muestra el formulario de inicio de sesión.
     *
     * @param Request $request
     * @return Response
     */
    public function mostrarFormularioLogin(Request $request): Response
    {
        return view('auth.login', [
            'titulo' => 'Iniciar Sesión',
            'exito' => session()->pull('exito'),
            'error' => session()->pull('error')
        ]);
    }

    /**
     * Procesa la solicitud de inicio de sesión.
     *
     * @param Request $request
     * @return Response
     */
    public function procesarLogin(Request $request): Response
    {
        $identificador = $request->post('identificador');
        $clave = $request->post('clave');

        $usuario = $this->usuarioService->autenticarUsuario($identificador, $clave);

        if ($usuario) {
            // AHORA QUE EL MIDDLEWARE DE SESIÓN ESTÁ ARREGLADO, UNA SESIÓN ESTÁ ACTIVA.
            // La función nativa de PHP para regenerar el ID debería funcionar correctamente.
            session_regenerate_id(true);

            // Guardamos la información del usuario en la sesión recién regenerada.
            $request->session()->set('usuarioId', $usuario->id);
            
            return redirect('/admin');
        }

        session()->set('error', 'Las credenciales proporcionadas no son correctas.');
        return redirect('/login');
    }
}