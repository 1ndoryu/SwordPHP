<?php

use App\model\Usuario;
use support\Log;

/**
 * Obtiene el modelo del usuario actualmente autenticado.
 *
 * Utiliza un caché en el objeto Request para evitar múltiples consultas a la base de datos
 * durante el ciclo de vida de una misma petición. Esta aproximación es segura para
 * entornos de ejecución persistentes como Workerman.
 *
 * @return Usuario|null El modelo del usuario o null si no está autenticado.
 */
function currentUser(): ?Usuario
{
    $request = request();

    // La propiedad 'swordUser' se usará como caché en el objeto de la petición.
    // Usamos property_exists para ser explícitos y evitar warnings.
    if (property_exists($request, 'swordUser')) {
        Log::channel('session_debug')->debug('Helper/currentUser: Devolviendo desde caché de la petición.', [
            'usuario_devuelto' => $request->swordUser ? 'ID: ' . $request->swordUser->id : 'null'
        ]);
        return $request->swordUser;
    }

    $idUsuario = session('usuarioId');

    Log::channel('session_debug')->info('Helper/currentUser: Buscando usuario en sesión (cache miss).', [
        'session_id' => session()->getId(),
        'usuarioId_obtenido' => $idUsuario,
        'session_data_completa' => session()->all()
    ]);

    if (!$idUsuario) {
        Log::channel('session_debug')->warning('Helper/currentUser: No se encontró usuarioId. Cacheando y devolviendo null.');
        // Cachear y devolver null
        return $request->swordUser = null;
    }

    $usuario = Usuario::find($idUsuario);

    Log::channel('session_debug')->info('Helper/currentUser: Búsqueda en BD finalizada.', [
        'buscado_por_id' => $idUsuario,
        'resultado' => $usuario ? 'Usuario encontrado (ID: ' . $usuario->id . ')' : 'Usuario NO encontrado en BD'
    ]);

    // Cachear el resultado (modelo de usuario o null) en el objeto de la petición y devolverlo.
    return $request->swordUser = $usuario;
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
