<?php

/**
 * Archivo para funciones de ayuda (helpers) globales.
 */

use App\model\Usuario;
use support\view\Blade;
use support\view\Raw;
use support\view\Twig;
use Webman\View;
use support\Container;

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

if (!function_exists('registrarAccionAjax')) {
    /**
     * Registra una acción AJAX para que esté disponible en el sistema.
     *
     * Esta es una función de conveniencia (helper) que simplifica el registro de acciones
     * desde cualquier parte del código, como el archivo functions.php de un tema.
     *
     * @param string $nombreAccion El nombre único para la acción AJAX.
     * @param callable $callback La función que se ejecutará cuando se llame a esta acción.
     */
    function registrarAccionAjax(string $nombreAccion, callable $callback)
    {
        \App\service\AjaxManagerService::registrarAccion($nombreAccion, $callback);
    }
}


#Funciona el test
registrarAccionAjax('test_sin_tema', function (support\Request $request) {
    $extra_data = $request->post('info', 'ninguna');
    return json([
        'success' => true,
        'message' => '¡Respuesta AJAX!',
        'info_recibida' => $extra_data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

/*
Ejemplo de uso.
function mi_manejador_de_accion( \support\Request $request ) {
    
    // Aquí va tu lógica. Por ejemplo, obtener un dato enviado por POST.
    $id_recibido = $request->post('id_item', 0);

    // Es importante que la función siempre devuelva una respuesta.
    // La función `json()` es una forma fácil de crear una respuesta JSON.
    return json([
        'success' => true,
        'message' => 'La acción se ha ejecutado correctamente para el item: ' . $id_recibido
    ]);
}

registrarAccionAjax('mi_accion_custom', 'mi_manejador_de_accion');

*/



if (!function_exists('view')) {
    /**
     * Render a view.
     *
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return \support\Response
     */
    function view(string $template, array $vars = [], string $app = null): \support\Response
    {
        return \support\view\View::render($template, $vars, $app);
    }
}


if (!function_exists('raw')) {
    /**
     * @param $template
     * @param array $vars
     * @param string|null $app
     * @return Raw
     */
    function raw($template, array $vars = [], string $app = null): Raw
    {
        return \support\view\View::handler(Raw::class, [
            'template' => $template,
            'vars' => $vars,
            'app' => $app
        ]);
    }
}


if (!function_exists('twig')) {
    /**
     * @param $template
     * @param array $vars
     * @param string|null $app
     * @return Twig
     */
    function twig($template, array $vars = [], string $app = null): Twig
    {
        return \support\view\View::handler(Twig::class, [
            'template' => $template,
            'vars' => $vars,
            'app' => $app
        ]);
    }
}

if (!function_exists('container')) {
    /**
     * Obtiene una instancia del contenedor de dependencias.
     *
     * @param string $id
     * @return mixed
     */
    function container(string $id)
    {
        return Container::get($id);
    }
}

if (!function_exists('encolarEstilo')) {
    /**
     * Encola una hoja de estilos para ser incluida en el head de la página.
     *
     * Wrapper para assetService()->encolarCss().
     *
     * @param string $identificador Un nombre único para el estilo.
     * @param string $ruta La ruta pública al archivo CSS.
     */
    function encolarEstilo(string $identificador, string $ruta): void
    {
        assetService()->encolarCss($identificador, $ruta);
    }
}

if (!function_exists('encolarScript')) {
    /**
     * Encola un script de JavaScript para ser incluido en el footer de la página.
     *
     * Wrapper para assetService()->encolarJs().
     *
     * @param string $identificador Un nombre único para el script.
     * @param string $ruta La ruta pública al archivo JS.
     */
    function encolarScript(string $identificador, string $ruta): void
    {
        assetService()->encolarJs($identificador, $ruta);
    }
}

if (!function_exists('rutaTema')) {
    /**
     * Devuelve la URL pública completa al directorio del tema activo.
     *
     * @param string $rutaAdicional Ruta opcional para añadir al final de la URL del tema.
     * @return string
     */
    function rutaTema(string $rutaAdicional = ''): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $themeDir = '/swordContent/themes/' . config('theme.active_theme');
        $finalPath = $baseUrl . $themeDir;

        if ($rutaAdicional) {
            $finalPath .= '/' . ltrim($rutaAdicional, '/');
        }

        return $finalPath;
    }
}



