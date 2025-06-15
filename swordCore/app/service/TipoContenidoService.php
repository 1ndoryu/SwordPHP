<?php

namespace Sword\SwordCore\app\service;

class TipoContenidoService
{
    private static ?TipoContenidoService $instancia = null;
    private array $tiposDeContenido = [];

    /**
     * El constructor es privado para prevenir la creación de nuevas instancias.
     */
    private function __construct() {}

    /**
     * Previene la clonación de la instancia.
     */
    private function __clone() {}

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
     * Registra un nuevo tipo de contenido en el sistema.
     *
     * @param string $slug El identificador único para el tipo de contenido.
     * @param array $argumentos La configuración para el tipo de contenido.
     */
    public function registrar(string $slug, array $argumentos): void
    {
        $this->tiposDeContenido[$slug] = $argumentos;
    }

    /**
     * Obtiene la configuración de un tipo de contenido específico.
     *
     * @param string $slug El slug del tipo de contenido a obtener.
     * @return array|null La configuración o null si no se encuentra.
     */
    public function obtener(string $slug): ?array
    {
        return $this->tiposDeContenido[$slug] ?? null;
    }

    /**
     * Devuelve todos los tipos de contenido registrados.
     *
     * @return array
     */
    public function obtenerTodos(): array
    {
        return $this->tiposDeContenido;
    }
}
