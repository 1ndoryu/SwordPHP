<?php

use support\view\Raw;
use support\view\Twig;
use App\service\TipoContenidoService;


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

if (!function_exists('renderizarPaginacion')) {
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

        if (empty($baseUrl)) {
            $baseUrl = request()->path();
        }

        $baseUrl = rtrim($baseUrl, '/');

        $html = '<nav aria-label="Navegación de páginas"><ul class="pagination">';

        $esPrimeraPagina = ($paginaActual <= 1);
        $html .= '<li class="page-item' . ($esPrimeraPagina ? ' disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl . '?page=' . ($paginaActual - 1)) . '" aria-label="Anterior">&lsaquo;</a>';
        $html .= '</li>';

        for ($i = 1; $i <= $totalPaginas; $i++) {
            $esPaginaActual = ($i == $paginaActual);
            if ($esPaginaActual) {
                $html .= '<li class="page-item active" aria-current="page"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($baseUrl . '?page=' . $i) . '">' . $i . '</a></li>';
            }
        }

        $esUltimaPagina = ($paginaActual >= $totalPaginas);
        $html .= '<li class="page-item' . ($esUltimaPagina ? ' disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl . '?page=' . ($paginaActual + 1)) . '" aria-label="Siguiente">&rsaquo;</a>';
        $html .= '</li>';

        $html .= '</ul></nav>';

        return $html;
    }
}

// swordCore/app/helpers/view.php

if (!function_exists('getHeader')) {
    /**
     * Carga el archivo header del tema.
     *
     * @param string|null $name El nombre de la cabecera especializada.
     */
    function getHeader($name = null)
    {
        $template = $name ? "layouts/header-{$name}.php" : 'layouts/header.php';
        // CORRECCIÓN: Usar el servicio de temas para obtener dinámicamente el tema activo.
        $header_path = SWORD_THEMES_PATH . '/' . \App\service\TemaService::getActiveTheme() . '/' . $template;

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
     * @param string|null $name El nombre del pie de página especializado.
     */
    function getFooter($name = null)
    {
        $template = $name ? "layouts/footer-{$name}.php" : 'layouts/footer.php';
        // CORRECCIÓN: Usar el servicio de temas para obtener dinámicamente el tema activo.
        $footer_path = SWORD_THEMES_PATH . '/' . \App\service\TemaService::getActiveTheme() . '/' . $template;

        if (file_exists($footer_path)) {
            include $footer_path;
        } else {
            trigger_error(sprintf('El archivo de pie de página "%s" no se encuentra en el tema activo.', $template), E_USER_WARNING);
        }
    }
}

if (!function_exists('renderizarMenuLateralAdmin')) {
    /**
     * Genera el HTML para el menú lateral del panel de administración.
     * Soporta submenús anidados.
     *
     * @return string El HTML del menú.
     */
    function renderizarMenuLateralAdmin()
    {
        $request = request();
        if (!$request) {
            return '';
        }

        // 1. Definir la estructura base del menú
        $menuItems = [
            'inicio' => ['url' => '/panel', 'text' => 'Inicio'],
            'paginas' => [
                'url' => '#',
                'text' => 'Páginas',
                'submenu' => [
                    'todas' => ['url' => '/panel/paginas', 'text' => 'Todas las páginas'],
                    'nueva' => ['url' => '/panel/paginas/create', 'text' => 'Añadir nueva'],
                ]
            ]
        ];

        // 2. Añadir los Tipos de Contenido Personalizados
        $tiposDeContenido = TipoContenidoService::getInstancia()->obtenerTodos();
        foreach ($tiposDeContenido as $slug => $config) {
            if ($slug === 'paginas') continue; // Ya se manejó

            $pluralName = $config['labels']['name'] ?? ucfirst($slug);
            $addNewItemText = $config['labels']['add_new_item'] ?? 'Añadir nuevo';

            $menuItems[$slug] = [
                'url' => '#',
                'text' => $pluralName,
                'submenu' => [
                    'todos' => ['url' => '/panel/' . $slug, 'text' => 'Todos'],
                    'nuevo' => ['url' => '/panel/' . $slug . '/crear', 'text' => $addNewItemText],
                    'ajustes' => ['url' => '/panel/' . $slug . '/ajustes', 'text' => 'Ajustes']
                ]
            ];
        }

        // 3. Añadir el resto de elementos estáticos del menú
        $menuItems['media'] = ['url' => '/panel/media', 'text' => 'Medios'];
        $menuItems['usuarios'] = ['url' => '/panel/usuarios', 'text' => 'Usuarios'];
        $menuItems['apariencia'] = [
            'url' => '#',
            'text' => 'Apariencia',
            'submenu' => [
                'temas' => ['url' => '/panel/temas', 'text' => 'Temas'],
            ]
        ];
        $menuItems['plugins'] = ['url' => '/panel/plugins', 'text' => 'Plugins'];
        $menuItems['ajustes'] = [
            'url' => '#',
            'text' => 'Ajustes',
            'submenu' => [
                'generales' => ['url' => '/panel/ajustes', 'text' => 'Generales'],
                'enlaces' => ['url' => '/panel/ajustes/enlaces-permanentes', 'text' => 'Enlaces Permanentes'],
            ]
        ];

        // 4. Aplicar el filtro para que los plugins puedan modificar el menú
        $menuItems = aplicarFiltro('menuLateralAdmin', $menuItems);

        // 5. Renderizar el HTML del menú
        $html = '';
        $currentPath = $request->path();

        foreach ($menuItems as $key => $item) {
            $hasSubmenu = !empty($item['submenu']) && is_array($item['submenu']);
            $isParentActive = false;

            if ($hasSubmenu) {
                foreach ($item['submenu'] as $subItem) {
                    if ($currentPath === $subItem['url'] || ($subItem['url'] !== '/panel' && str_starts_with($currentPath, $subItem['url']))) {
                        $isParentActive = true;
                        break;
                    }
                }
            } else {
                $isParentActive = ($currentPath === $item['url']) || ($item['url'] !== '/panel' && str_starts_with($currentPath, $item['url']));
                if ($item['url'] === '/panel' && $currentPath !== '/panel') {
                    $isParentActive = false;
                }
            }

            $parentActiveClass = $isParentActive ? 'active' : '';
            $parentOpenClass = $isParentActive ? 'open' : '';
            $linkTarget = $hasSubmenu ? '#' : $item['url'];

            $html .= "<li class=\"nav-item {$parentOpenClass}\">";
            $html .= "<a class=\"nav-link {$parentActiveClass}\" href=\"{$linkTarget}\"><span>{$item['text']}</span></a>";

            if ($hasSubmenu) {
                $html .= '<ul class="nav-submenu">';
                foreach ($item['submenu'] as $subKey => $subItem) {
                    // Un hijo es activo SÓLO si hay una coincidencia exacta de URL.
                    $isChildActive = ($currentPath === $subItem['url']);
                    $childActiveClass = $isChildActive ? 'active' : '';
                    $html .= "<li class=\"nav-item-sub\"><a class=\"nav-link-sub {$childActiveClass}\" href=\"{$subItem['url']}\">{$subItem['text']}</a></li>";
                }
                $html .= '</ul>';
            }
            $html .= '</li>';
        }

        return $html;
    }
}

if (!function_exists('partial')) {
    /**
     * Renderiza una vista parcial (componente) y devuelve su contenido como un string de HTML.
     *
     * @param string $template La ruta de la plantilla a renderizar.
     * @param array $vars Las variables que se pasarán a la plantilla.
     * @return string El HTML renderizado.
     */
    function partial(string $template, array $vars = []): string
    {
        return \support\view\NativePhpView::render($template, $vars);
    }
}
