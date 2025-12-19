<?php

namespace app\support;

use Closure;

/**
 * Adaptador que simula la API de Webman\Route para modo CGI.
 * Todas las llamadas se redirigen a CgiRouter.
 * 
 * Esto permite usar los mismos archivos de configuración de rutas
 * (config/route/*.php) tanto en modo Webman como en modo CGI,
 * eliminando la duplicación de código.
 */
class CgiRouteShim
{
    protected static string $prefixActual = '';
    protected static array $middlewareStack = [];
    protected array $middlewaresInstancia = [];
    protected ?int $grupoIndiceInicio = null;

    /**
     * Registra una ruta GET.
     */
    public static function get(string $path, $handler): self
    {
        return self::agregarRuta('GET', $path, $handler);
    }

    /**
     * Registra una ruta POST.
     */
    public static function post(string $path, $handler): self
    {
        return self::agregarRuta('POST', $path, $handler);
    }

    /**
     * Registra una ruta PUT.
     */
    public static function put(string $path, $handler): self
    {
        return self::agregarRuta('PUT', $path, $handler);
    }

    /**
     * Registra una ruta DELETE.
     */
    public static function delete(string $path, $handler): self
    {
        return self::agregarRuta('DELETE', $path, $handler);
    }

    /**
     * Registra una ruta PATCH.
     */
    public static function patch(string $path, $handler): self
    {
        return self::agregarRuta('PATCH', $path, $handler);
    }

    /**
     * Registra una ruta para cualquier método HTTP.
     */
    public static function any(string $path, $handler): self
    {
        return self::agregarRuta('ANY', $path, $handler);
    }

    /**
     * Registra una ruta OPTIONS.
     */
    public static function options(string $path, $handler): self
    {
        return self::agregarRuta('OPTIONS', $path, $handler);
    }

    /**
     * Registra una ruta HEAD.
     */
    public static function head(string $path, $handler): self
    {
        return self::agregarRuta('HEAD', $path, $handler);
    }

    /**
     * Define un grupo de rutas con prefijo opcional.
     * 
     * Soporta dos formas:
     * - Route::group('/prefix', function() { ... })
     * - Route::group(function() { ... })
     */
    public static function group($prefixOCallback, $callback = null): self
    {
        /* Determinar si el primer argumento es un prefijo o un callback */
        if (is_callable($prefixOCallback)) {
            $callback = $prefixOCallback;
            $prefix = '';
        } else {
            $prefix = $prefixOCallback;
        }

        /* Guardar estado anterior */
        $prefixAnterior = self::$prefixActual;

        /* Marcar inicio del grupo para aplicar middlewares después */
        $indiceInicio = CgiRouter::marcarInicioGrupo();

        /* Aplicar nuevo prefijo */
        self::$prefixActual .= $prefix;

        /* Ejecutar el callback que define las rutas del grupo */
        if (is_callable($callback)) {
            $callback();
        }

        /* Restaurar prefijo anterior */
        self::$prefixActual = $prefixAnterior;

        /* Crear instancia que recuerda el índice de inicio del grupo */
        $instancia = new self();
        $instancia->grupoIndiceInicio = $indiceInicio;

        return $instancia;
    }

    /**
     * Aplica middlewares a las últimas rutas registradas o al grupo.
     * 
     * Puede recibir:
     * - Un string: 'App\Middleware\Auth'
     * - Un array de strings: [Auth::class, Cors::class]
     * - Una instancia: new PermissionMiddleware('admin')
     */
    public function middleware($middleware): self
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        $this->middlewaresInstancia = array_merge($this->middlewaresInstancia, $middlewares);

        /* Convertir middlewares de Webman a CGI */
        $middlewaresCgi = self::convertirMiddlewares($middlewares);

        /* Si tenemos índice de inicio de grupo, usar el método optimizado */
        if ($this->grupoIndiceInicio !== null) {
            CgiRouter::aplicarMiddlewaresDesde($this->grupoIndiceInicio, $middlewaresCgi);
        } else {
            /* Fallback: aplicar a la última ruta registrada */
            $this->aplicarMiddlewareUltimaRuta($middlewaresCgi);
        }

        return $this;
    }

    /**
     * Aplica middlewares solo a la última ruta registrada.
     * Usado cuando ->middleware() se llama en una ruta individual.
     */
    protected function aplicarMiddlewareUltimaRuta(array $middlewares): void
    {
        $rutas = CgiRouter::obtenerRutas();

        if (empty($rutas)) {
            return;
        }

        $ultimoIndice = count($rutas) - 1;
        $rutas[$ultimoIndice]['middlewares'] = array_merge(
            $middlewares,
            $rutas[$ultimoIndice]['middlewares'] ?? []
        );

        CgiRouter::reemplazarRutas($rutas);
    }

    /**
     * Registra una ruta en CgiRouter.
     */
    protected static function agregarRuta(string $metodo, string $path, $handler): self
    {
        $rutaCompleta = self::$prefixActual . $path;

        /* Obtener middlewares del stack actual */
        $middlewares = self::obtenerMiddlewaresActuales();

        /* Convertir handler si es necesario */
        $handlerConvertido = self::convertirHandler($handler);

        CgiRouter::agregarRuta($metodo, $rutaCompleta, $handlerConvertido, $middlewares);

        return new self();
    }

    /**
     * Convierte el handler al formato esperado por CgiRouter.
     */
    protected static function convertirHandler($handler)
    {
        /* Si es un Closure, devolverlo directamente */
        if ($handler instanceof Closure) {
            return $handler;
        }

        /* Si es [Clase::class, 'metodo'], convertir a nombre de clase string */
        if (is_array($handler) && count($handler) === 2) {
            $clase = $handler[0];
            $metodo = $handler[1];

            /* Clase::class ya es un string, pero asegurarnos */
            if (is_string($clase)) {
                return [$clase, $metodo];
            }
        }

        return $handler;
    }

    /**
     * Obtiene los middlewares actuales del stack.
     */
    protected static function obtenerMiddlewaresActuales(): array
    {
        return self::$middlewareStack;
    }

    /**
     * Convierte middlewares de Webman a formato CGI.
     * 
     * Mapeo de middlewares:
     * - AdminAuth -> CgiAdminAuth
     * - JwtAuthentication -> (pendiente)
     */
    protected static function convertirMiddlewares(array $middlewares): array
    {
        $aliasMiddlewares = [
            'app\\middleware\\AdminAuth' => 'app\\middleware\\CgiAdminAuth',
        ];

        $resultado = [];
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                /* Buscar alias, si no existe usar el original */
                $clase = $aliasMiddlewares[$middleware] ?? $middleware;
                $resultado[] = $clase;
            } elseif (is_object($middleware)) {
                /* Instancia de middleware con parámetros */
                $resultado[] = $middleware;
            } else {
                $resultado[] = $middleware;
            }
        }

        return $resultado;
    }

    /**
     * Push middleware al stack (para grupos anidados).
     */
    public static function pushMiddleware(array $middlewares): void
    {
        self::$middlewareStack = array_merge(self::$middlewareStack, $middlewares);
    }

    /**
     * Pop middleware del stack.
     */
    public static function popMiddleware(int $cantidad): void
    {
        self::$middlewareStack = array_slice(self::$middlewareStack, 0, -$cantidad);
    }

    /**
     * Limpia el estado del shim (útil para testing).
     */
    public static function limpiar(): void
    {
        self::$prefixActual = '';
        self::$middlewareStack = [];
    }

    /**
     * Obtiene el prefijo actual (útil para debug).
     */
    public static function obtenerPrefixActual(): string
    {
        return self::$prefixActual;
    }
}
