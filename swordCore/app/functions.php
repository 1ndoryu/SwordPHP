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
use App\service\TipoContenidoService;
use App\model\Opcion;
use App\model\Pagina;
use App\service\AssetService;
use App\service\OpcionService;
use App\service\AjaxManagerService;

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

if (!function_exists('ajaxAccion')) {
    /**
     * Registra una acción AJAX para que esté disponible en el sistema.
     *
     * Esta es una función de conveniencia (helper) que simplifica el registro de acciones
     * desde cualquier parte del código, como el archivo functions.php de un tema.
     *
     * @param string $nombreAccion El nombre único para la acción AJAX.
     * @param callable $callback La función que se ejecutará cuando se llame a esta acción.
     */
    function ajaxAccion(string $nombreAccion, callable $callback)
    {
        \App\service\AjaxManagerService::registrarAccion($nombreAccion, $callback);
    }
}


#Funciona el test
ajaxAccion('test_sin_tema', function (support\Request $request) {
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

ajaxAccion('mi_accion_custom', 'mi_manejador_de_accion');

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



/**
 * Renderiza los controles de paginación en HTML usando PHP nativo.
 *
 * @param int $paginaActual La página que se está mostrando actualmente.
 * @param int $totalPaginas El número total de páginas disponibles.
 * @param string $baseUrl La URL base para los enlaces de paginación (sin el query string).
 * @return string El HTML de la paginación.
 */
function renderizarPaginacion(int $paginaActual, int $totalPaginas, string $baseUrl = ''): string
{
    if ($totalPaginas <= 1) {
        return '';
    }

    // Si no se provee una URL base, se usa la ruta actual.
    // Esto hace que la función sea reutilizable en diferentes secciones.
    if (empty($baseUrl)) {
        $baseUrl = request()->path();
    }

    // Se asegura de que la URL base no tenga una barra al final.
    $baseUrl = rtrim($baseUrl, '/');

    // Construcción del HTML
    $html = '<nav aria-label="Navegación de páginas"><ul class="pagination">';

    // Botón "Anterior"
    $esPrimeraPagina = ($paginaActual <= 1);
    $html .= '<li class="page-item' . ($esPrimeraPagina ? ' disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl . '?page=' . ($paginaActual - 1)) . '" aria-label="Anterior">&lsaquo;</a>';
    $html .= '</li>';

    // Enlaces numéricos de las páginas
    // Nota: Para sistemas con muchísimas páginas, se podría mejorar para mostrar un rango (ej. 1 ... 5, 6, 7 ... 20)
    for ($i = 1; $i <= $totalPaginas; $i++) {
        $esPaginaActual = ($i == $paginaActual);
        if ($esPaginaActual) {
            $html .= '<li class="page-item active" aria-current="page"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($baseUrl . '?page=' . $i) . '">' . $i . '</a></li>';
        }
    }

    // Botón "Siguiente"
    $esUltimaPagina = ($paginaActual >= $totalPaginas);
    $html .= '<li class="page-item' . ($esUltimaPagina ? ' disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl . '?page=' . ($paginaActual + 1)) . '" aria-label="Siguiente">&rsaquo;</a>';
    $html .= '</li>';

    $html .= '</ul></nav>';

    return $html;
}





// ... (resto de funciones existentes sin cambios)

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

if (!function_exists('getHeader')) {
    /**
     * Carga el archivo header del tema.
     *
     * Busca 'layouts/header.php' o 'layouts/header-{name}.php' en el directorio del tema activo.
     *
     * @param string|null $name El nombre de la cabecera especializada.
     */
    function getHeader($name = null)
    {
        $template = $name ? "layouts/header-{$name}.php" : 'layouts/header.php';
        $header_path = SWORD_THEMES_PATH . '/' . config('theme.active_theme') . '/' . $template;

        if (file_exists($header_path)) {
            include $header_path;
        } else {
            trigger_error(sprintf('El archivo de cabecera "%s" no se encuentra en el tema activo.', $template), E_USER_WARNING);
        }
    }
}

if (!function_exists('getFooter')) {
    /**
     * Carga el archivo footer del tema.
     *
     * Busca 'layouts/footer.php' o 'layouts/footer-{name}.php' en el directorio del tema activo.
     *
     * @param string|null $name El nombre del pie de página especializado.
     */
    function getFooter($name = null)
    {
        $template = $name ? "layouts/footer-{$name}.php" : 'layouts/footer.php';
        $footer_path = SWORD_THEMES_PATH . '/' . config('theme.active_theme') . '/' . $template;

        if (file_exists($footer_path)) {
            include $footer_path;
        } else {
            trigger_error(sprintf('El archivo de pie de página "%s" no se encuentra en el tema activo.', $template), E_USER_WARNING);
        }
    }
}

/**
 * Genera el HTML para el menú lateral del panel de administración.
 * El menú ahora se genera dinámicamente basado en los tipos de contenido registrados.
 *
 * @param Request $request La instancia de la petición actual para determinar la URL activa.
 * @return string El HTML del menú.
 */
function renderizarMenuLateralAdmin()
{
    $request = request();
    if (!$request) {
        return '';
    }

    // 1. Empezar con los items estáticos, incluyendo "Páginas"
    $menuItems = [
        'inicio' => ['url' => '/panel', 'icon' => 'fa-solid fa-house', 'text' => 'Inicio'],
        'paginas' => ['url' => '/panel/paginas', 'icon' => 'fa-solid fa-file-lines', 'text' => 'Páginas'],
    ];

    // 2. Obtener y añadir los OTROS tipos de contenido
    $tiposDeContenido = TipoContenidoService::getInstancia()->obtenerTodos();
    foreach ($tiposDeContenido as $slug => $config) {
        // Nos aseguramos de no añadir 'paginas' si estuviera registrado por error
        if ($slug === 'paginas') {
            continue;
        }
        $menuItems[$slug] = [
            'url'   => '/panel/' . $slug,
            'icon'  => $config['menu_icon'] ?? 'fa-solid fa-file-pen',
            'text'  => $config['labels']['name'] ?? ucfirst($slug),
        ];
    }

    // 3. Añadir el resto de items estáticos
    $menuItems['media'] = ['url' => '/panel/media', 'icon' => 'fa-solid fa-photo-film', 'text' => 'Medios'];
    $menuItems['usuarios'] = ['url' => '/panel/usuarios', 'icon' => 'fa-solid fa-users', 'text' => 'Usuarios'];
    $menuItems['ajustes'] = ['url' => '/panel/ajustes', 'icon' => 'fa-solid fa-gears', 'text' => 'Ajustes'];

    // 4. Lógica para renderizar el HTML (sin cambios)
    $html = '';
    foreach ($menuItems as $key => $item) {
        $isActive = (str_starts_with($request->path(), $item['url']) && $item['url'] !== '/panel') || $request->path() === $item['url'];
        if ($item['url'] === '/panel' && $request->path() !== '/panel') {
            $isActive = false;
        }
        $activeClass = $isActive ? 'active' : '';
        $html .= <<<HTML
            <li class="nav-item">
                <a class="nav-link {$activeClass}" href="{$item['url']}">
                    <i class="nav-icon {$item['icon']}"></i>
                    <span>{$item['text']}</span>
                </a>
            </li>
HTML;
    }

    return $html;
}

if (! function_exists('url_contenido')) {
    /**
     * Genera una URL relativa a la raíz para un recurso dentro de swordContent.
     *
     * @param string $ruta La ruta relativa al recurso desde la raíz de swordContent.
     * @return string La URL relativa completa (ej: /swordContent/media/archivo.jpg).
     */
    function url_contenido($ruta = ''): string
    {
        $basePath = '/swordContent';
        $rutaLimpia = ltrim($ruta, '/');
        // Asegura que no haya doble barra si $ruta empieza con '/'
        return rtrim($basePath, '/') . '/' . $rutaLimpia;
    }
}

/**
 * Registra un nuevo tipo de contenido en el sistema.
 * Es una función de conveniencia (wrapper) para el método registrar de TipoContenidoService.
 *
 * @param string $slug El identificador único para el tipo de contenido (ej: 'noticias').
 * @param array $argumentos La configuración para el tipo de contenido.
 */
function registrarTipoContenido(string $slug, array $argumentos)
{
    TipoContenidoService::getInstancia()->registrar($slug, $argumentos);
}

// -- Registros de Tipos de Contenido por Defecto --

registrarTipoContenido(
    'proyectos', // El slug para la URL: /panel/proyectos
    [
        'labels' => [
            'name'               => 'Proyectos',
            'singular_name'      => 'Proyecto',
            'add_new_item'       => 'Añadir nuevo proyecto',
            'edit_item'          => 'Editar proyecto',
            'new_item'           => 'Nuevo proyecto',
            'view_item'          => 'Ver proyecto',
            'search_items'       => 'Buscar proyectos',
            'not_found'          => 'No se encontraron proyectos',
            'not_found_in_trash' => 'No se encontraron proyectos en la papelera',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'fa-solid fa-briefcase', // Icono de FontAwesome
        'supports'     => ['title', 'editor'], 
    ]
);