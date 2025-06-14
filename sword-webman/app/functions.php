<?php

/**
 * Archivo para funciones de ayuda (helpers) globales.
 */

use App\model\Usuario;
// IDEA: Hacer una carpeta functions o otro nombre mejor, e incluirla toda en functions.php, y alli organizaremos funciones globales eficientemente
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

/**
 * Obtiene la instancia única del servicio de assets.
 *
 * Implementa un patrón Singleton para asegurar que solo exista una instancia
 * de AssetService durante el ciclo de vida de la petición.
 *
 * @return \App\service\AssetService La instancia del servicio de assets.
 */
function assetService(): \App\service\AssetService
{
    static $instancia = null;

    if ($instancia === null) {
        $instancia = new \App\service\AssetService();
    }

    return $instancia;
}

if (!function_exists('csrf_token')) {
    /**
     * Obtiene el valor del token CSRF actual.
     * Webman se encarga de generar y almacenar este token en la sesión.
     *
     * @return string
     */
    function csrf_token()
    {
        return session('_token', '');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Genera un campo de formulario input hidden con el token CSRF.
     * Esto es lo que la directiva @csrf de Blade necesita.
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Recupera los datos de "input antiguo" (old input) de la sesión.
     * Esto es útil para repopular formularios después de un error de validación.
     *
     * @param  string|null  $key La clave específica del input a recuperar.
     * @param  mixed  $default El valor por defecto si no se encuentra el input antiguo.
     * @return mixed
     */
    function old($key = null, $default = null)
    {
        // Pide a la sesión los datos que fueron "flasheados" como input antiguo.
        $old_input = session('_old_input');

        // Si no hay datos de input antiguo en la sesión, devolvemos el valor por defecto.
        if (is_null($old_input)) {
            return $default;
        }

        // Si se pide una clave específica (ej: old('nombre')), la buscamos.
        if (!is_null($key)) {
            // Devolvemos el valor del input antiguo si existe para esa clave,
            // si no, el valor por defecto.
            return $old_input[$key] ?? $default;
        }

        // Si no se pide ninguna clave, devolvemos todo el array de input antiguo.
        return $old_input;
    }
}
