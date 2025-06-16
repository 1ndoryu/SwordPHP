<?php

use App\model\Usuario;

/**
 * Obtiene el modelo del usuario actualmente autenticado.
 *
 * Utiliza un caché estático para evitar múltiples consultas a la base de datos
 * durante el ciclo de vida de una misma petición.
 *
 * @return Usuario|null El modelo del usuario o null si no está autenticado.
 */
function usuarioActual(): ?Usuario
{
    static $usuarioActual = null;
    static $haSidoVerificado = false;

    if ($haSidoVerificado) {
        return $usuarioActual;
    }

    $haSidoVerificado = true;
    $idUsuario = session('usuarioId');

    if (!$idUsuario) {
        return null;
    }

    $usuarioActual = Usuario::find($idUsuario);
    return $usuarioActual;
}

/**
 * Obtiene el ID del usuario actualmente autenticado.
 *
 * @return int|null El ID del usuario o null si no está autenticado.
 */
function idUsuarioActual(): ?int
{
    return session('usuarioId');
}

if (!function_exists('obtenerMetaUsuario')) {
    /**
     * Obtiene un metadato de usuario.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $meta_key La clave del metadato.
     * @param bool $single Si se debe devolver un solo valor.
     * @return mixed
     */
    function obtenerMetaUsuario(int $usuario_id, string $meta_key, bool $single = true)
    {
        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return null;
        }
        return $usuario->obtenerMeta($meta_key, $single);
    }
}

if (!function_exists('guardarMetaUser')) {
    /**
     * Guarda (crea o actualiza) un metadato de usuario.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $meta_key La clave del metadato.
     * @param mixed $meta_value El valor del metadato.
     * @return bool
     */
    function guardarMetaUser(int $usuario_id, string $meta_key, $meta_value): bool
    {
        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return false;
        }
        return $usuario->guardarMeta($meta_key, $meta_value);
    }
}

if (!function_exists('eliminarMetaUser')) {
    /**
     * Elimina un metadato de usuario.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $meta_key La clave del metadato.
     * @return bool
     */
    function eliminarMetaUser(int $usuario_id, string $meta_key): bool
    {
        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return false;
        }
        return $usuario->eliminarMeta($meta_key);
    }
}
