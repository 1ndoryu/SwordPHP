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
     * Es una buena práctica para mantener el código desacoplado y fácil de probar.
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
        // Renderiza la vista que crearemos en el siguiente paso.
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
        // Obtenemos todos los datos enviados por POST.
        $datos = $request->post();

        // En una aplicación real, aquí deberías añadir una capa de validación robusta
        // para asegurar que 'nombreUsuario', 'correoElectronico' y 'clave' existen y son válidos.

        $usuario = $this->usuarioService->crearUsuario($datos);

        if ($usuario) {
            // CAMBIO: Primero guardamos el mensaje en la sesión.
            session()->set('exito', '¡Cuenta creada correctamente! Ya puedes iniciar sesión.');
            // Luego, retornamos la redirección.
            return redirect('/login');
        }

        // CAMBIO: Hacemos lo mismo para el caso de error.
        session()->set('error', 'No se pudo crear la cuenta. El email o nombre de usuario puede que ya esté en uso.');
        return redirect('/registro');
    }
}