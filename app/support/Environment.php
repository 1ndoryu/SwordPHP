<?php

namespace app\support;

/**
 * Clase para detectar el entorno de ejecución de SwordPHP.
 * Permite determinar si la aplicación corre en modo Webman (persistente)
 * o en modo CGI tradicional (Apache/Nginx).
 */
class Environment
{
    private static ?bool $esWebman = null;
    private static ?bool $esCgi = null;

    /**
     * Determina si estamos ejecutando en modo Webman (proceso persistente).
     * Webman/Workerman define la constante WEBMAN_VERSION o tiene el Worker activo.
     */
    public static function esWebman(): bool
    {
        if (self::$esWebman === null) {
            self::$esWebman = defined('WORKERMAN_VERSION')
                || class_exists('Workerman\\Worker', false)
                || (isset($GLOBALS['worker']) && $GLOBALS['worker'] !== null);
        }

        return self::$esWebman;
    }

    /**
     * Determina si estamos ejecutando en modo CGI tradicional.
     * Es lo opuesto a Webman.
     */
    public static function esCgi(): bool
    {
        if (self::$esCgi === null) {
            self::$esCgi = !self::esWebman();
        }

        return self::$esCgi;
    }

    /**
     * Fuerza el modo de ejecución (útil para testing).
     */
    public static function forzarModo(bool $esWebman): void
    {
        self::$esWebman = $esWebman;
        self::$esCgi = !$esWebman;
    }

    /**
     * Reinicia la detección de entorno.
     */
    public static function reiniciar(): void
    {
        self::$esWebman = null;
        self::$esCgi = null;
    }

    /**
     * Obtiene información del entorno actual.
     */
    public static function obtenerInfo(): array
    {
        return [
            'modo' => self::esWebman() ? 'webman' : 'cgi',
            'php_sapi' => PHP_SAPI,
            'servidor' => $_SERVER['SERVER_SOFTWARE'] ?? 'desconocido',
            'sistema' => PHP_OS,
        ];
    }
}
