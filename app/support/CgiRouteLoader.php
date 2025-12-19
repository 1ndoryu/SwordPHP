<?php

namespace app\support;

/**
 * Cargador de Rutas para modo CGI.
 * Lee las definiciones de rutas de config/route/*.php y las registra
 * en el CgiRouter sin necesidad de Webman.
 */
class CgiRouteLoader
{
    protected static array $middlewareStack = [];
    protected static string $prefixStack = '';

    /**
     * Carga todas las rutas desde los archivos de configuración.
     */
    public static function cargar(): void
    {
        $rutasDir = base_path('config/route');

        if (!is_dir($rutasDir)) {
            return;
        }

        foreach (glob("{$rutasDir}/*.php") as $archivo) {
            self::cargarArchivo($archivo);
        }
    }

    /**
     * Carga un archivo de rutas.
     * Redefine temporalmente la clase Route para capturar las definiciones.
     */
    protected static function cargarArchivo(string $archivo): void
    {
        /* Crear alias temporal para capturar rutas */
        require $archivo;
    }

    /* 
     * Métodos estáticos que simulan la API de Webman\Route
     * para ser usados cuando se cargan las rutas en modo CGI.
     */

    public static function get(string $path, $handler): RouteRegistrar
    {
        return self::agregarRuta('GET', $path, $handler);
    }

    public static function post(string $path, $handler): RouteRegistrar
    {
        return self::agregarRuta('POST', $path, $handler);
    }

    public static function put(string $path, $handler): RouteRegistrar
    {
        return self::agregarRuta('PUT', $path, $handler);
    }

    public static function delete(string $path, $handler): RouteRegistrar
    {
        return self::agregarRuta('DELETE', $path, $handler);
    }

    public static function patch(string $path, $handler): RouteRegistrar
    {
        return self::agregarRuta('PATCH', $path, $handler);
    }

    public static function any(string $path, $handler): RouteRegistrar
    {
        return self::agregarRuta('ANY', $path, $handler);
    }

    public static function group($prefix, $callback = null): RouteRegistrar
    {
        /* group('/prefix', callback) o group(callback) */
        if (is_callable($prefix)) {
            $callback = $prefix;
            $prefix = '';
        }

        $prefixAnterior = self::$prefixStack;
        self::$prefixStack = rtrim(self::$prefixStack . '/' . trim($prefix, '/'), '/');

        $registrar = new RouteRegistrar();

        /* Ejecutar callback para registrar rutas del grupo */
        if (is_callable($callback)) {
            $callback();
        }

        self::$prefixStack = $prefixAnterior;

        return $registrar;
    }

    protected static function agregarRuta(string $metodo, string $path, $handler): RouteRegistrar
    {
        $rutaCompleta = self::$prefixStack . '/' . ltrim($path, '/');
        $rutaCompleta = '/' . trim($rutaCompleta, '/');

        CgiRouter::agregarRuta($metodo, $rutaCompleta, $handler, self::$middlewareStack);

        return new RouteRegistrar();
    }
}

/**
 * Registrador de rutas que permite encadenar middlewares.
 */
class RouteRegistrar
{
    protected array $middlewares = [];

    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            foreach ($middleware as $m) {
                $this->middlewares[] = $m;
            }
        } else {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function name(string $name): self
    {
        /* Los nombres de ruta no se usan en CGI por ahora */
        return $this;
    }
}
