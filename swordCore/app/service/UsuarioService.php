<?php

namespace App\service;

use App\model\Usuario;
use Throwable;
use support\Log;

class UsuarioService
{
    /**
     * Crea un nuevo usuario en la base de datos.
     * Asigna el rol 'admin' al primer usuario y 'suscriptor' a los demás.
     *
     * @param array $datosUsuario Los datos del usuario a crear.
     * @return Usuario|null El modelo del usuario creado o null si falla.
     */
    public function crearUsuario(array $datosUsuario): ?Usuario
    {
        try {
            // Se comprueba si es el primer usuario en registrarse.
            $esPrimerUsuario = Usuario::count() === 0;

            // Se asigna el rol correspondiente.
            $datosUsuario['rol'] = $esPrimerUsuario ? 'admin' : 'suscriptor';

            // Se hashea la clave antes de guardar.
            $datosUsuario['clave'] = password_hash($datosUsuario['clave'], PASSWORD_BCRYPT);

            return Usuario::create($datosUsuario);
        } catch (Throwable $e) {
            // Se añade un log detallado para diagnosticar el problema.
            Log::channel('default')->error(
                'Error al crear el usuario: ' . $e->getMessage(),
                ['exception' => $e]
            );
            // Devolvemos null para mantener el comportamiento original.
            return null;
        }
    }

    /**
     * Verifica si una contraseña en texto plano coincide con una hasheada.
     *
     * @param string $clavePlana La contraseña enviada por el usuario.
     * @param string $claveHasheada La contraseña almacenada en la base de datos.
     * @return bool True si la contraseña es correcta, false en caso contrario.
     */
    public function verificarClave(string $clavePlana, string $claveHasheada): bool
    {
        return password_verify($clavePlana, $claveHasheada);
    }

    /**
     * Autentica a un usuario basado en un identificador y una contraseña.
     *
     * @param string $identificador El correo electrónico o nombre de usuario.
     * @param string $clavePlana La contraseña en texto plano.
     * @return Usuario|null El modelo del usuario si la autenticación es exitosa, de lo contrario null.
     */
    public function autenticarUsuario(string $identificador, string $clavePlana): ?Usuario
    {
        $usuario = Usuario::where('correoelectronico', $identificador)
            ->orWhere('nombreusuario', $identificador)
            ->first();

        if (!$usuario) {
            return null;
        }

        if ($this->verificarClave($clavePlana, $usuario->clave)) {
            return $usuario;
        }

        return null;
    }

    /**
     * Obtiene una lista paginada de todos los usuarios.
     *
     * @param int $porPagina
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function obtenerUsuariosPaginados(int $porPagina = 15)
    {
        // Ordenamos por fecha de creación descendente para ver los más nuevos primero.
        return Usuario::latest()->paginate($porPagina);
    }
}
