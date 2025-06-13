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
}