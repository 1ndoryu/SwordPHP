<?php

/**
 * Archivo para funciones de ayuda (helpers) globales.
 * Se organiza incluyendo ficheros especializados desde la carpeta /helpers.
 */

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

// Carga de helpers especializados
require_once __DIR__ . '/helpers/user.php';

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

function initIconos()
{

    $GLOBALS['iconos'] = [
        'edit' => '<svg data-testid="geist-icon" height="16" stroke-linejoin="round" style="color:currentColor" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.75 0.189331L12.2803 0.719661L15.2803 3.71966L15.8107 4.24999L15.2803 4.78032L5.15901 14.9016C4.45575 15.6049 3.50192 16 2.50736 16H0.75H0V15.25V13.4926C0 12.4981 0.395088 11.5442 1.09835 10.841L11.2197 0.719661L11.75 0.189331ZM11.75 2.31065L9.81066 4.24999L11.75 6.18933L13.6893 4.24999L11.75 2.31065ZM2.15901 11.9016L8.75 5.31065L10.6893 7.24999L4.09835 13.841C3.67639 14.2629 3.1041 14.5 2.50736 14.5H1.5V13.4926C1.5 12.8959 1.73705 12.3236 2.15901 11.9016ZM9 16H16V14.5H9V16Z" fill="currentColor"></path></svg>',
        'borrar' => '<svg data-testid="geist-icon" height="16" stroke-linejoin="round" style="color:currentColor" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.75 2.75C6.75 2.05964 7.30964 1.5 8 1.5C8.69036 1.5 9.25 2.05964 9.25 2.75V3H6.75V2.75ZM5.25 3V2.75C5.25 1.23122 6.48122 0 8 0C9.51878 0 10.75 1.23122 10.75 2.75V3H12.9201H14.25H15V4.5H14.25H13.8846L13.1776 13.6917C13.0774 14.9942 11.9913 16 10.6849 16H5.31508C4.00874 16 2.92263 14.9942 2.82244 13.6917L2.11538 4.5H1.75H1V3H1.75H3.07988H5.25ZM4.31802 13.5767L3.61982 4.5H12.3802L11.682 13.5767C11.6419 14.0977 11.2075 14.5 10.6849 14.5H5.31508C4.79254 14.5 4.3581 14.0977 4.31802 13.5767Z" fill="currentColor"></path></svg>',
        'file' => '<svg data-testid="geist-icon" height="16" stroke-linejoin="round" style="color:currentColor" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" clip-rule="evenodd" d="M14.5 6.5V13.5C14.5 14.8807 13.3807 16 12 16H4C2.61929 16 1.5 14.8807 1.5 13.5V1.5V0H3H8H9.08579C9.351 0 9.60536 0.105357 9.79289 0.292893L14.2071 4.70711C14.3946 4.89464 14.5 5.149 14.5 5.41421V6.5ZM13 6.5V13.5C13 14.0523 12.5523 14.5 12 14.5H4C3.44772 14.5 3 14.0523 3 13.5V1.5H8V5V6.5H9.5H13ZM9.5 2.12132V5H12.3787L9.5 2.12132Z" fill="currentColor"></path></svg>',
        'user' => '<svg data-testid="geist-icon" height="16" stroke-linejoin="round" style="color:currentColor" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.75 0C5.95507 0 4.5 1.45507 4.5 3.25V3.75C4.5 5.54493 5.95507 7 7.75 7H8.25C10.0449 7 11.5 5.54493 11.5 3.75V3.25C11.5 1.45507 10.0449 0 8.25 0H7.75ZM6 3.25C6 2.2835 6.7835 1.5 7.75 1.5H8.25C9.2165 1.5 10 2.2835 10 3.25V3.75C10 4.7165 9.2165 5.5 8.25 5.5H7.75C6.7835 5.5 6 4.7165 6 3.75V3.25ZM2.5 14.5V13.1709C3.31958 11.5377 4.99308 10.5 6.82945 10.5H9.17055C11.0069 10.5 12.6804 11.5377 13.5 13.1709V14.5H2.5ZM6.82945 9C4.35483 9 2.10604 10.4388 1.06903 12.6857L1 12.8353V13V15.25V16H1.75H14.25H15V15.25V13V12.8353L14.931 12.6857C13.894 10.4388 11.6452 9 9.17055 9H6.82945Z" fill="currentColor"></path></svg>',
        'logosword' => '<?xml version="1.0" encoding="UTF-8"?><svg id="uuid-1b5301d2-d414-428d-b552-75223c85e691" data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10.94 13.88"><path d="m9.07,4.5c.13.13.16.29.08.45-.41.84-.81,1.67-1.22,2.51-.16.32-.14.66-.09,1,.04.36.23.65.43.94.63.91,1.25,1.83,1.87,2.75.25.36.49.73.74,1.09.11.16.09.38-.05.52-.14.14-.35.16-.51.06-1.11-.75-2.21-1.5-3.32-2.25-.29-.2-.56-.42-.87-.58-.52-.28-1.08-.36-1.63-.11-.83.38-1.64.79-2.45,1.19-.21.1-.37.07-.52-.1-.13-.15-.13-.33,0-.51.44-.66.88-1.32,1.31-2,.23-.37.25-.78.21-1.21-.04-.4-.24-.72-.46-1.04-.66-.94-1.31-1.9-1.96-2.85-.18-.26-.36-.53-.55-.79-.11-.16-.09-.38.05-.52.14-.15.36-.16.53-.04,1.2.82,2.4,1.65,3.6,2.47.27.18.52.38.86.45.44.09.87.08,1.28-.1.24-.1.45-.27.67-.41.5-.33,1-.66,1.5-.99.17-.11.37-.09.52.06Z"/><path d="m5.52,2.82s-.05-.09-.02-.14c.12-.25.24-.5.37-.75.05-.1.04-.2.03-.3-.01-.11-.07-.2-.13-.28-.19-.27-.38-.55-.56-.83-.07-.11-.15-.22-.22-.33-.03-.05-.03-.11.02-.16.04-.04.11-.05.15-.02.33.23.66.45,1,.68.09.06.17.13.26.18.16.08.32.11.49.03.25-.11.49-.24.74-.36.06-.03.11-.02.16.03.04.04.04.1,0,.15-.13.2-.27.4-.39.6-.07.11-.08.24-.06.36.01.12.07.22.14.31.2.28.39.57.59.86.05.08.11.16.16.24.03.05.03.11-.02.16-.04.04-.11.05-.16.01-.36-.25-.72-.5-1.08-.74-.08-.06-.16-.11-.26-.14-.13-.03-.26-.02-.39.03-.07.03-.13.08-.2.12-.15.1-.3.2-.45.3-.05.03-.11.03-.15-.02Z"/></svg>',
        'checkCircle' => '<svg data-testid="geist-icon" height="16" stroke-linejoin="round" style="color:currentColor" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" clip-rule="evenodd" d="M14.5 8C14.5 11.5899 11.5899 14.5 8 14.5C4.41015 14.5 1.5 11.5899 1.5 8C1.5 4.41015 4.41015 1.5 8 1.5C11.5899 1.5 14.5 4.41015 14.5 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8ZM11.5303 6.53033L12.0607 6L11 4.93934L10.4697 5.46967L6.5 9.43934L5.53033 8.46967L5 7.93934L3.93934 9L4.46967 9.53033L5.96967 11.0303C6.26256 11.3232 6.73744 11.3232 7.03033 11.0303L11.5303 6.53033Z" fill="currentColor"></path></svg>',
    ];
}

function icon($nombre)
{
    if (isset($GLOBALS['iconos'][$nombre])) {
        return $GLOBALS['iconos'][$nombre];
    }
    return '<i class="icono icono-default"></i>'; // Icono por defecto si no se encuentra
}
initIconos();


use support\Response;

/**
 * INICIO DE LA NUEVA FUNCIÓN
 * Hemos añadido esta función para simplificar la inclusión de vistas parciales.
 */
if (!function_exists('partial')) {
    /**
     * Renderiza una vista parcial (componente) y devuelve su contenido como un string de HTML.
     * Es ideal para incluir componentes reutilizables dentro de otras vistas.
     *
     * @param string $template La ruta de la plantilla a renderizar (ej: 'admin/components/gestor-metadatos').
     * @param array $vars Las variables que se pasarán a la plantilla.
     * @return string El HTML renderizado.
     */
    function partial(string $template, array $vars = []): string
    {
        // Llama directamente al método render de la clase NativePhpView,
        // que está configurada en tu proyecto para devolver un string.
        return \support\view\NativePhpView::render($template, $vars);
    }
}