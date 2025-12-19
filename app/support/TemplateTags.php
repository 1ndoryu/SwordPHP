<?php

/**
 * Template Tags para SwordPHP
 * 
 * Funciones helper disponibles en las plantillas de temas.
 * Nomenclatura en español con camelCase.
 * 
 * Convención:
 * - elXxx()      → Imprime directamente (echo)
 * - obtenerXxx() → Retorna el valor
 */

use app\support\ThemeEngine;
use app\model\Content;
use app\model\Option;

/* 
 *
 * Funciones de Cabecera y Pie
 *
 */

if (!function_exists('obtenerCabecera')) {
    /**
     * Incluye el archivo header.php del tema
     * 
     * @param string|null $nombre Variante del header (ej: 'home' para header-home.php)
     */
    function obtenerCabecera(?string $nombre = null): void
    {
        $tema = ThemeEngine::instancia();
        $rutaPlantillas = $tema->obtenerRutaPlantillas();

        if ($nombre) {
            $archivo = $rutaPlantillas . "/header-{$nombre}.php";
            if (file_exists($archivo)) {
                require $archivo;
                return;
            }
        }

        $archivo = $rutaPlantillas . '/header.php';
        if (file_exists($archivo)) {
            require $archivo;
        }
    }
}

if (!function_exists('obtenerPie')) {
    /**
     * Incluye el archivo footer.php del tema
     * 
     * @param string|null $nombre Variante del footer
     */
    function obtenerPie(?string $nombre = null): void
    {
        $tema = ThemeEngine::instancia();
        $rutaPlantillas = $tema->obtenerRutaPlantillas();

        if ($nombre) {
            $archivo = $rutaPlantillas . "/footer-{$nombre}.php";
            if (file_exists($archivo)) {
                require $archivo;
                return;
            }
        }

        $archivo = $rutaPlantillas . '/footer.php';
        if (file_exists($archivo)) {
            require $archivo;
        }
    }
}

if (!function_exists('obtenerParcial')) {
    /**
     * Incluye un archivo parcial del tema
     * 
     * @param string $nombre Nombre del parcial (sin extension)
     * @param array $datos Variables a pasar al parcial
     */
    function obtenerParcial(string $nombre, array $datos = []): void
    {
        $tema = ThemeEngine::instancia();
        $archivo = $tema->obtenerRutaPlantillas() . "/partials/{$nombre}.php";

        if (file_exists($archivo)) {
            extract($datos);
            require $archivo;
        }
    }
}

/* 
 *
 * Funciones de Contenido
 *
 */

if (!function_exists('elTitulo')) {
    /**
     * Imprime el título del contenido actual
     */
    function elTitulo(): void
    {
        global $contenido;
        echo htmlspecialchars(obtenerTitulo(), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('obtenerTitulo')) {
    /**
     * Obtiene el título del contenido actual
     * 
     * @return string
     */
    function obtenerTitulo(): string
    {
        global $contenido;
        return $contenido['content_data']['title'] ?? $contenido['title'] ?? '';
    }
}

if (!function_exists('elContenido')) {
    /**
     * Imprime el contenido HTML
     */
    function elContenido(): void
    {
        global $contenido;
        echo obtenerContenido();
    }
}

if (!function_exists('obtenerContenido')) {
    /**
     * Obtiene el contenido HTML
     * 
     * @return string
     */
    function obtenerContenido(): string
    {
        global $contenido;
        return $contenido['content_data']['content'] ?? $contenido['content'] ?? '';
    }
}

if (!function_exists('elExcerpto')) {
    /**
     * Imprime el extracto del contenido
     * 
     * @param int $longitud Longitud máxima en caracteres
     */
    function elExcerpto(int $longitud = 150): void
    {
        echo htmlspecialchars(obtenerExcerpto($longitud), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('obtenerExcerpto')) {
    /**
     * Obtiene el extracto del contenido
     * 
     * @param int $longitud Longitud máxima
     * @return string
     */
    function obtenerExcerpto(int $longitud = 150): string
    {
        global $contenido;

        $excerpto = $contenido['content_data']['excerpt'] ?? null;

        if (!$excerpto) {
            $contenidoHtml = obtenerContenido();
            $textoPlano = strip_tags($contenidoHtml);
            $excerpto = mb_substr($textoPlano, 0, $longitud);

            if (mb_strlen($textoPlano) > $longitud) {
                $excerpto .= '...';
            }
        }

        return $excerpto;
    }
}

if (!function_exists('elEnlace')) {
    /**
     * Imprime el enlace permanente al contenido
     */
    function elEnlace(): void
    {
        echo obtenerEnlace();
    }
}

if (!function_exists('obtenerEnlace')) {
    /**
     * Obtiene el enlace permanente al contenido
     * 
     * @return string
     */
    function obtenerEnlace(): string
    {
        global $contenido;
        $slug = $contenido['slug'] ?? '';
        return urlSitio() . '/' . $slug;
    }
}

/* 
 *
 * Funciones de Imagen
 *
 */

if (!function_exists('laImagen')) {
    /**
     * Imprime la imagen destacada
     * 
     * @param string $clase Clase CSS para la imagen
     * @param string $tamano Tamaño de la imagen (futuro)
     */
    function laImagen(string $clase = '', string $tamano = 'full'): void
    {
        $url = obtenerUrlImagen();

        if ($url) {
            $clasesHtml = $clase ? " class=\"{$clase}\"" : '';
            $alt = htmlspecialchars(obtenerTitulo(), ENT_QUOTES, 'UTF-8');
            echo "<img src=\"{$url}\"{$clasesHtml} alt=\"{$alt}\">";
        }
    }
}

if (!function_exists('obtenerUrlImagen')) {
    /**
     * Obtiene la URL de la imagen destacada
     * 
     * @return string|null
     */
    function obtenerUrlImagen(): ?string
    {
        global $contenido;
        return $contenido['content_data']['featured_image'] ?? null;
    }
}

if (!function_exists('tieneImagen')) {
    /**
     * Verifica si el contenido tiene imagen destacada
     * 
     * @return bool
     */
    function tieneImagen(): bool
    {
        return obtenerUrlImagen() !== null;
    }
}

/* 
 *
 * Funciones de Query
 *
 */

if (!function_exists('obtenerPosts')) {
    /**
     * Obtiene una lista de posts
     * 
     * @param array $args Argumentos de la consulta
     * @return array
     */
    function obtenerPosts(array $args = []): array
    {
        $porDefecto = [
            'tipo' => 'post',
            'estado' => 'published',
            'limite' => 10,
            'pagina' => 1,
            'orden' => 'created_at',
            'direccion' => 'desc'
        ];

        $args = array_merge($porDefecto, $args);

        $query = Content::where('status', $args['estado']);

        if ($args['tipo'] !== '*') {
            $query->where('type', $args['tipo']);
        }

        $query->orderBy($args['orden'], $args['direccion']);

        $offset = ($args['pagina'] - 1) * $args['limite'];
        $query->offset($offset)->limit($args['limite']);

        return $query->get()->toArray();
    }
}

if (!function_exists('obtenerPost')) {
    /**
     * Obtiene un post por slug o ID
     * 
     * @param string|int $identificador Slug o ID del post
     * @return array|null
     */
    function obtenerPost(string|int $identificador): ?array
    {
        if (is_numeric($identificador)) {
            $post = Content::find($identificador);
        } else {
            $post = Content::where('slug', $identificador)
                ->where('status', 'published')
                ->first();
        }

        return $post ? $post->toArray() : null;
    }
}

/* 
 *
 * Funciones de Sitio
 *
 */

if (!function_exists('urlSitio')) {
    /**
     * Obtiene la URL base del sitio
     * 
     * @return string
     */
    function urlSitio(): string
    {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return rtrim("{$protocolo}://{$host}", '/');
    }
}

if (!function_exists('urlInicio')) {
    /**
     * Alias de urlSitio()
     * 
     * @return string
     */
    function urlInicio(): string
    {
        return urlSitio();
    }
}

if (!function_exists('urlAsset')) {
    /**
     * Obtiene la URL completa de un asset del tema
     * 
     * @param string $ruta Ruta relativa al asset
     * @return string
     */
    function urlAsset(string $ruta): string
    {
        $tema = ThemeEngine::instancia();
        return urlSitio() . $tema->obtenerUrlAssets() . '/' . ltrim($ruta, '/');
    }
}

if (!function_exists('nombreSitio')) {
    /**
     * Obtiene el nombre del sitio
     * 
     * @return string
     */
    function nombreSitio(): string
    {
        return get_option('site_name', 'SwordPHP');
    }
}

if (!function_exists('descripcionSitio')) {
    /**
     * Obtiene la descripción/tagline del sitio
     * 
     * @return string
     */
    function descripcionSitio(): string
    {
        return get_option('site_description', '');
    }
}

if (!function_exists('elNombreSitio')) {
    /**
     * Imprime el nombre del sitio
     */
    function elNombreSitio(): void
    {
        echo htmlspecialchars(nombreSitio(), ENT_QUOTES, 'UTF-8');
    }
}

/* 
 *
 * Funciones de Metadatos
 *
 */

if (!function_exists('obtenerMeta')) {
    /**
     * Obtiene un campo meta del contenido actual
     * 
     * @param string $clave Clave del meta
     * @param mixed $defecto Valor por defecto
     * @return mixed
     */
    function obtenerMeta(string $clave, $defecto = null)
    {
        global $contenido;
        return $contenido['content_data'][$clave] ?? $defecto;
    }
}

if (!function_exists('elMeta')) {
    /**
     * Imprime un campo meta del contenido
     * 
     * @param string $clave Clave del meta
     */
    function elMeta(string $clave): void
    {
        $valor = obtenerMeta($clave, '');
        echo htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

/* 
 *
 * Funciones de Fecha
 *
 */

if (!function_exists('laFecha')) {
    /**
     * Imprime la fecha del contenido
     * 
     * @param string $formato Formato de fecha (PHP date format)
     */
    function laFecha(string $formato = 'd/m/Y'): void
    {
        echo obtenerFecha($formato);
    }
}

if (!function_exists('obtenerFecha')) {
    /**
     * Obtiene la fecha formateada del contenido
     * 
     * @param string $formato Formato de fecha
     * @return string
     */
    function obtenerFecha(string $formato = 'd/m/Y'): string
    {
        global $contenido;
        $fecha = $contenido['created_at'] ?? null;

        if (!$fecha) {
            return '';
        }

        try {
            $dateTime = new DateTime($fecha);
            return $dateTime->format($formato);
        } catch (\Throwable $e) {
            return '';
        }
    }
}

/* 
 *
 * Funciones de Head
 *
 */

if (!function_exists('laCabezaSeo')) {
    /**
     * Imprime las etiquetas meta SEO básicas
     */
    function laCabezaSeo(): void
    {
        global $contenido;

        $titulo = obtenerTitulo() ?: nombreSitio();
        $descripcion = obtenerExcerpto(160) ?: descripcionSitio();
        $imagen = obtenerUrlImagen();
        $url = urlSitio() . ($_SERVER['REQUEST_URI'] ?? '/');

        echo "<title>" . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "</title>\n";
        echo "<meta name=\"description\" content=\"" . htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') . "\">\n";

        /* Open Graph */
        echo "<meta property=\"og:title\" content=\"" . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "\">\n";
        echo "<meta property=\"og:description\" content=\"" . htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') . "\">\n";
        echo "<meta property=\"og:url\" content=\"" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\">\n";

        if ($imagen) {
            echo "<meta property=\"og:image\" content=\"" . htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        echo "<meta property=\"og:type\" content=\"website\">\n";
    }
}
