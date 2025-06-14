<?php

namespace App\service;

/**
 * Servicio para la gestión de assets (CSS y JavaScript).
 *
 * Permite encolar archivos, directorios, código en línea y HTML arbitrario
 * para ser impresos en el layout, manteniendo el control sobre los recursos.
 */
class AssetService
{
    /** @var array<string, string> */
    private array $estilos = []; // Clave: identificador, Valor: ruta

    /** @var array<string, string> */
    private array $scripts = []; // Clave: identificador, Valor: ruta

    /** @var array<string> */
    private array $codigoCssEnLinea = [];

    /** @var array<string> */
    private array $codigoJsEnLinea = [];

    /** @var array<string> */
    private array $htmlHead = [];

    /** @var array<string> */
    private array $htmlFooter = [];

    /**
     * Añade una hoja de estilos a la cola de renderizado.
     *
     * @param string $identificador Un nombre único para este asset (ej: 'main-styles').
     * @param string $ruta La ruta pública al archivo CSS (ej: '/css/app.css').
     * @return void
     */
    public function encolarCss(string $identificador, string $ruta): void
    {
        if (!isset($this->estilos[$identificador])) {
            $this->estilos[$identificador] = $ruta;
        }
    }

    /**
     * Añade un script de JavaScript a la cola de renderizado.
     *
     * @param string $identificador Un nombre único para este asset (ej: 'app-logic').
     * @param string $ruta La ruta pública al archivo JS (ej: '/js/app.js').
     * @return void
     */
    public function encolarJs(string $identificador, string $ruta): void
    {
        if (!isset($this->scripts[$identificador])) {
            $this->scripts[$identificador] = $ruta;
        }
    }

    /**
     * Encola todos los archivos de un directorio específico (CSS o JS).
     *
     * @param string $rutaDirectorio Ruta al directorio, relativa a la carpeta `public`.
     * @param string $tipo 'css' o 'js'.
     * @return void
     */
    public function encolarDirectorio(string $rutaDirectorio, string $tipo = 'js'): void
    {
        $directorioAbsoluto = public_path() . DIRECTORY_SEPARATOR . ltrim($rutaDirectorio, '/\\');

        if (!is_dir($directorioAbsoluto)) {
            return;
        }

        $extension = '.' . $tipo;
        try {
            $iterador = new \DirectoryIterator($directorioAbsoluto);
            foreach ($iterador as $archivo) {
                if ($archivo->isFile() && str_ends_with(strtolower($archivo->getFilename()), $extension)) {
                    $rutaRelativa = rtrim($rutaDirectorio, '/\\') . '/' . $archivo->getFilename();
                    $identificador = 'dir-' . pathinfo($archivo->getFilename(), PATHINFO_FILENAME);

                    if ($tipo === 'css') {
                        $this->encolarCss($identificador, $rutaRelativa);
                    } else {
                        $this->encolarJs($identificador, $rutaRelativa);
                    }
                }
            }
        } catch (\Exception $e) {
            // Opcional: loguear excepción (ej: permisos de lectura)
        }
    }

    /**
     * Agrega un bloque de código CSS para ser impreso en el head.
     *
     * @param string $codigo El código CSS sin las etiquetas <style>.
     * @return void
     */
    public function agregarCssEnLinea(string $codigo): void
    {
        $this->codigoCssEnLinea[] = trim($codigo);
    }

    /**
     * Agrega un bloque de código JavaScript para ser impreso en el footer.
     *
     * @param string $codigo El código JavaScript sin las etiquetas <script>.
     * @return void
     */
    public function agregarJsEnLinea(string $codigo): void
    {
        $this->codigoJsEnLinea[] = trim($codigo);
    }

    /**
     * Agrega una cadena de HTML para ser impresa en la sección head.
     * Útil para meta tags, favicons, etc.
     *
     * @param string $html
     * @return void
     */
    public function agregarHtmlHead(string $html): void
    {
        $this->htmlHead[] = $html;
    }

    /**
     * Agrega una cadena de HTML para ser impresa justo antes de </body>.
     * Útil para plantillas de JS, SVGs, etc.
     *
     * @param string $html
     * @return void
     */
    public function agregarHtmlFooter(string $html): void
    {
        $this->htmlFooter[] = $html;
    }

    /**
     * Genera todo el HTML para la sección <head>.
     *
     * @return string
     */
    public function imprimirAssetsHead(): string
    {
        $html = '';
        foreach ($this->estilos as $ruta) {
            $html .= '<link rel="stylesheet" href="' . htmlspecialchars($ruta) . '">' . PHP_EOL;
        }
        if (!empty($this->codigoCssEnLinea)) {
            $html .= '<style>' . PHP_EOL . implode(PHP_EOL, $this->codigoCssEnLinea) . PHP_EOL . '</style>' . PHP_EOL;
        }
        if (!empty($this->htmlHead)) {
            $html .= implode(PHP_EOL, $this->htmlHead) . PHP_EOL;
        }
        return $html;
    }

    /**
     * Genera todo el HTML para el final del <body>.
     *
     * @return string
     */
    public function imprimirAssetsFooter(): string
    {
        $html = '';
        foreach ($this->scripts as $ruta) {
            $html .= '<script src="' . htmlspecialchars($ruta) . '"></script>' . PHP_EOL;
        }
        if (!empty($this->codigoJsEnLinea)) {
            $html .= '<script>' . PHP_EOL . implode(PHP_EOL, $this->codigoJsEnLinea) . PHP_EOL . '</script>' . PHP_EOL;
        }
        if (!empty($this->htmlFooter)) {
            $html .= implode(PHP_EOL, $this->htmlFooter) . PHP_EOL;
        }
        return $html;
    }
}