<?php

/**
 * Archivo de helpers para la creación de temas (Theming).
 * Contiene las funciones del "Loop" de SwordPHP, análogas a las de WordPress.
 */

// Variables globales para el loop principal.
$GLOBALS['swordConsultaPrincipal'] = null;
$GLOBALS['entrada'] = null;

if (!function_exists('havePost')) {
    /**
     * Determina si la consulta principal actual tiene entradas para el loop.
     *
     * @return bool
     */
    function havePost(): bool
    {
        global $swordConsultaPrincipal;
        if (!$swordConsultaPrincipal instanceof \App\service\SwordQuery) {
            return false;
        }
        return $swordConsultaPrincipal->havePost();
    }
}

if (!function_exists('thePost')) {
    /**
     * Itera el índice de entradas y configura la variable global $entrada.
     *
     * @return void
     */
    function thePost(): void
    {
        global $swordConsultaPrincipal, $entrada;
        if ($swordConsultaPrincipal instanceof \App\service\SwordQuery) {
            $swordConsultaPrincipal->thePost();
            // Sincroniza la variable global $entrada con la entrada actual de la consulta.
            $entrada = $swordConsultaPrincipal->entrada;
        }
    }
}

// A partir de aquí, las funciones asumen que la variable global $entrada está disponible
// gracias a la llamada previa de thePost().

if (!function_exists('postId')) {
    /**
     * Muestra el ID de la entrada actual.
     *
     * @return void
     */
    function postId(): void
    {
        echo getPostId();
    }
}

if (!function_exists('getPostId')) {
    /**
     * Obtiene el ID de la entrada actual.
     *
     * @return int
     */
    function getPostId(): int
    {
        global $entrada;
        return $entrada->id ?? 0;
    }
}

if (!function_exists('theTitle')) {
    /**
     * Muestra el título de la entrada actual, aplicando filtros.
     *
     * @return void
     */
    function theTitle(): void
    {
        global $entrada;
        $titulo = $entrada->titulo ?? '';
        // Se aplica un filtro para permitir la modificación del título.
        echo applyFilters('theTitle', $titulo, $entrada);
    }
}

if (!function_exists('getTitle')) {
    /**
     * Obtiene el título de la entrada actual sin aplicar filtros.
     *
     * @return string
     */
    function getTitle(): string
    {
        global $entrada;
        return $entrada->titulo ?? '';
    }
}

if (!function_exists('theContent')) {
    /**
     * Muestra el contenido de la entrada actual, aplicando filtros.
     *
     * @return void
     */
    function theContent(): void
    {
        global $entrada;
        $contenido = $entrada->contenido ?? '';
        // Se aplica un filtro para permitir el parseo de shortcodes, etc.
        echo applyFilters('theContent', $contenido, $entrada);
    }
}

if (!function_exists('getContent')) {
    /**
     * Obtiene el contenido de la entrada actual sin aplicar filtros.
     *
     * @return string
     */
    function getContent(): string
    {
        global $entrada;
        return $entrada->contenido ?? '';
    }
}

if (!function_exists('getPermalink')) {
    /**
     * Muestra la URL (enlace permanente) de la entrada actual.
     *
     * @return void
     */
    function getPermalink(): void
    {
        echo getPermalink();
    }
}

if (!function_exists('getPermalinkPost')) {
    /**
     * Obtiene la URL (enlace permanente) para una entrada específica.
     *
     * @param \App\model\Pagina $entrada El objeto de la entrada (página, post, etc.).
     * @return string La URL completa del enlace permanente.
     */
    function getPermalinkPost(\App\model\Pagina $entrada): string
    {
        $opcionService = new \App\service\OpcionService();
        $estructura = $opcionService->getOption('permalink_structure', '/%slug%/');

        $reemplazos = [
            '%año%' => $entrada->created_at->format('Y'),
            '%mes%' => $entrada->created_at->format('m'),
            '%dia%' => $entrada->created_at->format('d'),
            '%slug%' => $entrada->slug,
            '%id%'   => $entrada->id,
        ];

        $rutaRelativa = str_replace(array_keys($reemplazos), array_values($reemplazos), $estructura);

        $rutaLimpia = trim($rutaRelativa, '/');
        $baseUrl = rtrim(config('app.url', ''), '/');

        return $baseUrl . '/' . $rutaLimpia;
    }
}

if (!function_exists('getPermalink')) {
    /**
     * Obtiene la URL (enlace permanente) de la entrada actual del loop.
     *
     * @return string
     */
    function getPermalink(): string
    {
        global $entrada;
        if (!$entrada instanceof \App\model\Pagina) {
            return '';
        }
        return getPermalinkPost($entrada);
    }
}

if (!function_exists('sw_head')) {
    /**
     * Dispara la acción 'sw_head' e imprime los assets del head.
     * Análogo a wp_head(). Debe colocarse antes de la etiqueta </head>.
     */
    function sw_head()
    {
        // Imprime los <link> y <style> de los CSS encolados.
        echo assetService()->imprimirAssetsHead();

        // Hook para que plugins o el tema puedan inyectar contenido en el <head>.
        doAction('sw_head');
    }
}

if (!function_exists('sw_footer')) {
    /**
     * Dispara la acción 'sw_footer' e imprime los assets del footer.
     * Análogo a wp_footer(). Debe colocarse antes de la etiqueta </body>.
     */
    function sw_footer()
    {
        // Imprime los <script> de los JS encolados.
        echo assetService()->imprimirAssetsFooter();

        // Hook para que plugins o el tema puedan inyectar contenido en el <footer>.
        doAction('sw_footer');
    }
}

if (!function_exists('sw_admin_head')) {
    /**
     * Dispara la acción 'admin_head' e imprime los assets del head del panel.
     * Debe colocarse antes de la etiqueta </head> en el layout del panel.
     */
    function sw_admin_head()
    {
        // Imprime los <link> y <style> de los CSS encolados para el panel.
        echo assetService()->imprimirAssetsHead();
        
        // Hook para que plugins puedan inyectar contenido en el <head> del panel.
        doAction('admin_head');
    }
}

if (!function_exists('sw_admin_footer')) {
    /**
     * Dispara la acción 'admin_footer' e imprime los assets del footer del panel.
     * Debe colocarse antes de la etiqueta </body> en el layout del panel.
     */
    function sw_admin_footer()
    {
        // Hook para que plugins puedan añadir contenido antes de cerrar el body del panel.
        doAction('admin_footer');

        // Imprime las etiquetas <script> de los JS encolados.
        echo assetService()->imprimirAssetsFooter();
    }
}