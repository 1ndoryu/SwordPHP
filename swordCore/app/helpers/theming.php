<?php

/**
 * Archivo de helpers para la creación de temas (Theming).
 * Contiene las funciones del "Loop" de SwordPHP, análogas a las de WordPress.
 */

// Variables globales para el loop principal.
$GLOBALS['swordConsultaPrincipal'] = null;
$GLOBALS['entrada'] = null;

if (!function_exists('hayEntradas')) {
    /**
     * Determina si la consulta principal actual tiene entradas para el loop.
     *
     * @return bool
     */
    function hayEntradas(): bool
    {
        global $swordConsultaPrincipal;
        if (!$swordConsultaPrincipal instanceof \App\service\SwordQuery) {
            return false;
        }
        return $swordConsultaPrincipal->hayEntradas();
    }
}

if (!function_exists('laEntrada')) {
    /**
     * Itera el índice de entradas y configura la variable global $entrada.
     *
     * @return void
     */
    function laEntrada(): void
    {
        global $swordConsultaPrincipal, $entrada;
        if ($swordConsultaPrincipal instanceof \App\service\SwordQuery) {
            $swordConsultaPrincipal->laEntrada();
            // Sincroniza la variable global $entrada con la entrada actual de la consulta.
            $entrada = $swordConsultaPrincipal->entrada;
        }
    }
}

// A partir de aquí, las funciones asumen que la variable global $entrada está disponible
// gracias a la llamada previa de laEntrada().

if (!function_exists('elId')) {
    /**
     * Muestra el ID de la entrada actual.
     *
     * @return void
     */
    function elId(): void
    {
        echo obtenerElId();
    }
}

if (!function_exists('obtenerElId')) {
    /**
     * Obtiene el ID de la entrada actual.
     *
     * @return int
     */
    function obtenerElId(): int
    {
        global $entrada;
        return $entrada->id ?? 0;
    }
}

if (!function_exists('elTitulo')) {
    /**
     * Muestra el título de la entrada actual, aplicando filtros.
     *
     * @return void
     */
    function elTitulo(): void
    {
        global $entrada;
        $titulo = $entrada->titulo ?? '';
        // Se aplica un filtro para permitir la modificación del título.
        echo aplicarFiltro('elTitulo', $titulo, $entrada);
    }
}

if (!function_exists('obtenerElTitulo')) {
    /**
     * Obtiene el título de la entrada actual sin aplicar filtros.
     *
     * @return string
     */
    function obtenerElTitulo(): string
    {
        global $entrada;
        return $entrada->titulo ?? '';
    }
}

if (!function_exists('elContenido')) {
    /**
     * Muestra el contenido de la entrada actual, aplicando filtros.
     *
     * @return void
     */
    function elContenido(): void
    {
        global $entrada;
        $contenido = $entrada->contenido ?? '';
        // Se aplica un filtro para permitir el parseo de shortcodes, etc.
        echo aplicarFiltro('elContenido', $contenido, $entrada);
    }
}

if (!function_exists('obtenerElContenido')) {
    /**
     * Obtiene el contenido de la entrada actual sin aplicar filtros.
     *
     * @return string
     */
    function obtenerElContenido(): string
    {
        global $entrada;
        return $entrada->contenido ?? '';
    }
}

if (!function_exists('elEnlacePermanente')) {
    /**
     * Muestra la URL (enlace permanente) de la entrada actual.
     *
     * @return void
     */
    function elEnlacePermanente(): void
    {
        echo obtenerEnlacePermanente();
    }
}

if (!function_exists('obtenerEnlacePermanente')) {
    /**
     * Obtiene la URL (enlace permanente) de la entrada actual.
     *
     * @return string
     */
    function obtenerEnlacePermanente(): string
    {
        global $entrada;
        if (!isset($entrada->slug)) {
            return '';
        }

        // TODO: En el futuro, usar un helper de URL del sitio si existe.
        $baseUrl = rtrim(config('app.url', ''), '/');
        return $baseUrl . '/' . $entrada->slug;
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
        hacerAccion('sw_head');
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
        hacerAccion('sw_footer');
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
        hacerAccion('admin_head');
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
        hacerAccion('admin_footer');

        // Imprime las etiquetas <script> de los JS encolados.
        echo assetService()->imprimirAssetsFooter();
    }
}