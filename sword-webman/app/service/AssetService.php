<?php

namespace App\service;

/**
 * Servicio para la gestión de assets (CSS y JavaScript).
 *
 * Permite encolar archivos CSS y JS desde cualquier parte de la aplicación
 * para luego ser impresos en el layout principal, evitando la duplicación
 * y manteniendo el control sobre los recursos cargados.
 */
class AssetService
{
    /**
     * Almacena las rutas de los archivos CSS encolados.
     * El índice es un identificador único para el asset.
     *
     * @var array<string, string>
     */
    private array $estilos = [];

    /**
     * Almacena las rutas de los archivos JavaScript encolados.
     * El índice es un identificador único para el asset.
     *
     * @var array<string, string>
     */
    private array $scripts = [];

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
     * Genera las etiquetas <link> para todos los CSS encolados.
     *
     * @return string El HTML de las etiquetas <link>.
     */
    public function imprimirEstilos(): string
    {
        $html = '';
        foreach ($this->estilos as $ruta) {
            $html .= '<link rel="stylesheet" href="' . htmlspecialchars($ruta) . '">' . PHP_EOL;
        }
        return $html;
    }

    /**
     * Genera las etiquetas <script> para todos los JS encolados.
     *
     * @return string El HTML de las etiquetas <script>.
     */
    public function imprimirScripts(): string
    {
        $html = '';
        foreach ($this->scripts as $ruta) {
            $html .= '<script src="' . htmlspecialchars($ruta) . '"></script>' . PHP_EOL;
        }
        return $html;
    }
}
