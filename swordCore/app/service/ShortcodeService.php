<?php

namespace App\service;

/**
 * Servicio para gestionar el sistema de Shortcodes.
 * Implementa el patrón Singleton para un registro único.
 */
class ShortcodeService
{
    private static ?self $instancia = null;

    /**
     * Almacena los callbacks de los shortcodes registrados.
     * @var array<string, callable>
     */
    private array $shortcodes = [];

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
     * Registra un nuevo shortcode y su callback.
     *
     * @param string $tag El tag del shortcode (ej: 'saludo').
     * @param callable $callback La función a ejecutar.
     */
    public function registrar(string $tag, callable $callback): void
    {
        $this->shortcodes[$tag] = $callback;
    }

    /**
     * Procesa una cadena de contenido, buscando y ejecutando los shortcodes.
     *
     * @param string $contenido El contenido a procesar.
     * @return string El contenido con los shortcodes reemplazados.
     */
    public function procesar(string $contenido): string
    {
        if (empty($this->shortcodes)) {
            return $contenido;
        }

        // Expresión regular para encontrar shortcodes.
        // Soporta: [tag], [tag attr="val"], y [tag]contenido[/tag]
        $regex = '/\[(\w+)([^\]]*)\](?:(.+?)\[\/\1\])?/s';

        return preg_replace_callback($regex, [$this, 'ejecutarShortcode'], $contenido);
    }

    /**
     * Callback para preg_replace_callback que ejecuta el shortcode encontrado.
     *
     * @param array $matches Coincidencias de la expresión regular.
     * @return string
     * @internal
     */
    private function ejecutarShortcode(array $matches): string
    {
        $tag = $matches[1];

        // Si el tag encontrado no está registrado, devolvemos el texto original del shortcode.
        if (!isset($this->shortcodes[$tag])) {
            return $matches[0];
        }

        // Parsear atributos
        $atributos = $this->parsearAtributos($matches[2] ?? '');

        // El contenido del shortcode (para shortcodes de cierre)
        $contenidoShortcode = $matches[3] ?? null;

        // Llamar al callback del shortcode
        return call_user_func($this->shortcodes[$tag], $atributos, $contenidoShortcode, $tag);
    }

    /**
     * Parsea la cadena de atributos de un shortcode.
     *
     * @param string $textoAtributos La cadena de atributos (ej: ' foo="bar" baz="qux"').
     * @return array Array asociativo de atributos.
     * @internal
     */
    private function parsearAtributos(string $textoAtributos): array
    {
        $atributos = [];
        // Regex para encontrar pares clave="valor" o clave='valor'
        if (preg_match_all('/(\w+)\s*=\s*("([^"]*)"|\'([^\']*)\')/', $textoAtributos, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // $match[1] es la clave, $match[3] o $match[4] es el valor
                $atributos[$match[1]] = $match[3] ?: $match[4];
            }
        }
        return $atributos;
    }
}
