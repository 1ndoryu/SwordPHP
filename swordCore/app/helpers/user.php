<?php

use App\model\Usuario;
use support\Log;

/**
 * Obtiene el modelo del usuario actualmente autenticado.
 *
 * Utiliza un caché estático para evitar múltiples consultas a la base de datos
 * durante el ciclo de vida de una misma petición.
 *
 * @return Usuario|null El modelo del usuario o null si no está autenticado.
 */
function currentUser(): ?Usuario
{
    static $currentUser = null;
    static $haSidoVerificado = false;

    Log::channel('session_debug')->debug('Helper/currentUser: Iniciando verificación.', [
        'haSidoVerificado' => $haSidoVerificado,
        'usuarioCacheado' => $currentUser ? 'ID: ' . $currentUser->id : null,
    ]);

    if ($haSidoVerificado) {
        Log::channel('session_debug')->debug('Helper/currentUser: Devolviendo desde caché estático.', [
            'usuario_devuelto' => $currentUser ? 'ID: ' . $currentUser->id : 'null'
        ]);
        return $currentUser;
    }

    $haSidoVerificado = true;
    $idUsuario = session('usuarioId');

    Log::channel('session_debug')->info('Helper/currentUser: Intentando obtener usuario de la sesión.', [
        'session_id' => session()->getId(),
        'usuarioId_obtenido' => $idUsuario,
        'session_data_completa' => session()->all()
    ]);

    if (!$idUsuario) {
        Log::channel('session_debug')->warning('Helper/currentUser: No se encontró usuarioId en la sesión. Devolviendo null.');
        return null;
    }

    $currentUser = Usuario::find($idUsuario);

    Log::channel('session_debug')->info('Helper/currentUser: Búsqueda en BD finalizada.', [
        'buscado_por_id' => $idUsuario,
        'resultado' => $currentUser ? 'Usuario encontrado (ID: ' . $currentUser->id . ')' : 'Usuario NO encontrado en BD'
    ]);

    return $currentUser;
}

/**
 * Obtiene el ID del usuario actualmente autenticado.
 *
 * @return int|null El ID del usuario o null si no está autenticado.
 */
function idCurrentUser(): ?int
{
    return session('usuarioId');
}

if (!function_exists('getMetaUser')) {
    /**
     * Obtiene un metadato de usuario.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $meta_key La clave del metadato.
     * @param bool $single Si se debe devolver un solo valor.
     * @return mixed
     */
    function getMetaUser(int $usuario_id, string $meta_key, bool $single = true)
    {
        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return null;
        }
        return $usuario->obtenerMeta($meta_key, $single);
    }
}

if (!function_exists('updateMetaUser')) {
    /**
     * Guarda (crea o actualiza) un metadato de usuario.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $meta_key La clave del metadato.
     * @param mixed $meta_value El valor del metadato.
     * @return bool
     */
    function updateMetaUser(int $usuario_id, string $meta_key, $meta_value): bool
    {
        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return false;
        }
        return $usuario->guardarMeta($meta_key, $meta_value);
    }
}

if (!function_exists('deleteMetaUser')) {
    /**
     * Elimina un metadato de usuario.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $meta_key La clave del metadato.
     * @return bool
     */
    function deleteMetaUser(int $usuario_id, string $meta_key): bool
    {
        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return false;
        }
        return $usuario->eliminarMeta($meta_key);
    }
}
