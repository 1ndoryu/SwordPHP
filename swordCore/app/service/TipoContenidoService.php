<?php

namespace App\service;

/**
 * Servicio de Registro Central para Tipos de Contenido (Post Types).
 *
 * Implementa el patrón Singleton para garantizar una única fuente de verdad
 * para todos los tipos de contenido registrados durante una petición.
 */
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
     * Previene la deserialización de la instancia.
     */
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
     * Registra un nuevo tipo de contenido en el sistema.
     *
     * @param string $slug El identificador único para el tipo de contenido (ej: 'noticias').
     * @param array $argumentos La configuración para el tipo de contenido.
     */
    public function registrar(string $slug, array $argumentos): void
    {
        // Aquí se podrían añadir validaciones para los argumentos en el futuro.
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