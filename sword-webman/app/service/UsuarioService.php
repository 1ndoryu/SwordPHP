<?php

namespace App\service;

use App\model\Usuario;
use Throwable;

class UsuarioService
{
    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param array $datosUsuario Los datos del usuario a crear. Debe incluir 'nombreUsuario', 'correoElectronico' y 'clave'.
     * @return Usuario|null El modelo del usuario creado o null si falla la creación.
     */
    public function crearUsuario(array $datosUsuario): ?Usuario
    {
        try {
            // Hashear la contraseña antes de guardarla. ¡Nunca guardar contraseñas en texto plano!
            $datosUsuario['clave'] = password_hash($datosUsuario['clave'], PASSWORD_BCRYPT);

            // Usar el método create de Eloquent, que aprovecha la propiedad $fillable del modelo.
            return Usuario::create($datosUsuario);
        } catch (Throwable $e) {

            exit;

            return null; // Dejamos esto comentado por ahora.
        }
    }

    /**
     * Verifica si una contraseña en texto plano coincide con una hasheada.
     *
     * @param string $clavePlana La contraseña enviada por el usuario (ej: desde el form de login).
     * @param string $claveHasheada La contraseña almacenada en la base de datos.
     * @return bool True si la contraseña es correcta, false en caso contrario.
     */
    public function verificarClave(string $clavePlana, string $claveHasheada): bool
    {
        return password_verify($clavePlana, $claveHasheada);
    }

    /**
     * Autentica a un usuario basado en un identificador (email o nombre de usuario) y una contraseña.
     *
     * @param string $identificador El correo electrónico o nombre de usuario.
     * @param string $clavePlana La contraseña en texto plano.
     * @return Usuario|null El modelo del usuario si la autenticación es exitosa, de lo contrario null.
     */
    public function autenticarUsuario(string $identificador, string $clavePlana): ?Usuario
    {
        // Buscamos al usuario por correo electrónico o por nombre de usuario.
        $usuario = Usuario::where('correoelectronico', $identificador)
            ->orWhere('nombreusuario', $identificador)
            ->first();

        // Si no encontramos un usuario, retornamos null.
        if (!$usuario) {
            return null;
        }

        // Verificamos si la contraseña proporcionada coincide con la hasheada en la BD.
        if ($this->verificarClave($clavePlana, $usuario->clave)) {
            // Si la clave es correcta, retornamos el objeto del usuario.
            return $usuario;
        }

        // Si la clave es incorrecta, retornamos null.
        return null;
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
            // 1. Obtenemos el objeto sesión del framework.
            // Esto asegura que la sesión se inicie correctamente antes de manipularla.
            $session = $request->session();

            // 2. Regeneramos el ID de la sesión usando el método del framework.
            // Esto previene ataques de fijación de sesión (session fixation).
            // Es la forma correcta en Webman, en lugar de usar session_regenerate_id() de PHP.
            $session->regenerate(true);

            // 3. Guardamos la información del usuario en la sesión recién regenerada.
            $session->set('usuarioId', $usuario->id);

            return redirect('/admin');
        }

        session()->set('error', 'Las credenciales proporcionadas no son correctas.');
        return redirect('/login');
    }
}
