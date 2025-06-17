<?php

namespace App\service;

/**
 * Servicio para registrar y gestionar las páginas de administración añadidas por los plugins.
 * Implementa el patrón Singleton para mantener un registro único de páginas.
 */
class PluginPageService
{
    private static ?self $instancia = null;
    /**
     * Almacena las páginas registradas por los plugins.
     * La estructura es: ['plugin-slug' => ['page_title' => '...', 'callback' => ...]]
     * @var array<string, array>
     */
    private array $paginasRegistradas = [];

    private function __construct() {}
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar un singleton.");
    }

    /**
     * Obtiene la instancia única del servicio.
     */
    public static function getInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Registra una nueva página de administración para un plugin.
     *
     * @param string $slug El slug único para la página (generalmente el del plugin).
     * @param array $opciones Un array de opciones para la página, que debe incluir:
     * - 'page_title' (string) El título que aparecerá en la cabecera de la página.
     * - 'callback' (callable) La función que se ejecutará para renderizar el contenido.
     * @return void
     */
    public function registrar(string $slug, array $opciones): void
    {
        if (!isset($opciones['page_title']) || !isset($opciones['callback']) || !is_callable($opciones['callback'])) {
            // Ignoramos el registro inválido para no romper la ejecución.
            // En un futuro se podría añadir un log de errores aquí.
            return;
        }
        $this->paginasRegistradas[$slug] = $opciones;
    }

    /**
     * Obtiene la configuración de una página de administración registrada.
     *
     * @param string $slug El slug de la página a obtener.
     * @return array|null La configuración o null si no se encuentra.
     */
    public function obtener(string $slug): ?array
    {
        return $this->paginasRegistradas[$slug] ?? null;
    }
}
