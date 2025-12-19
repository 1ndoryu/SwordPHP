<?php

namespace app\support;

use Closure;
use Throwable;
use ReflectionClass;
use ReflectionMethod;

/**
 * Router CGI para SwordPHP.
 * Parsea y ejecuta las rutas definidas en config/route/*.php
 * sin necesidad de Workerman/Webman.
 */
class CgiRouter
{
    protected static array $rutas = [];
    protected static array $grupos = [];
    protected static array $middlewaresGlobales = [];
    protected static ?self $instancia = null;

    /**
     * Obtiene la instancia singleton.
     */
    public static function instancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Carga las rutas desde los archivos de configuración.
     */
    public function cargarRutas(): void
    {
        $rutasDir = base_path('config/route');

        if (!is_dir($rutasDir)) {
            return;
        }

        foreach (glob("{$rutasDir}/*.php") as $archivo) {
            $this->cargarArchivoRutas($archivo);
        }
    }

    /**
     * Carga un archivo de rutas parseando las llamadas a Route::*.
     */
    protected function cargarArchivoRutas(string $archivo): void
    {
        $contenido = file_get_contents($archivo);

        /* Parsear grupos y rutas del archivo */
        $this->parsearRutas($contenido);
    }

    /**
     * Parsea el contenido PHP y extrae las definiciones de rutas.
     * Esta es una implementación simplificada que lee las rutas directamente.
     */
    protected function parsearRutas(string $contenido): void
    {
        /* 
         * Estrategia: En lugar de parsear el PHP, cargamos las rutas 
         * desde la configuración de Webman que ya está parseada.
         * Esto es más confiable y mantiene compatibilidad.
         */
    }

    /**
     * Registra una ruta manualmente.
     */
    public static function agregarRuta(
        string $metodo,
        string $patron,
        $handler,
        array $middlewares = []
    ): void {
        self::$rutas[] = [
            'metodo' => strtoupper($metodo),
            'patron' => $patron,
            'regex' => self::patronARegex($patron),
            'handler' => $handler,
            'middlewares' => $middlewares,
            'params' => self::extraerNombresParams($patron),
        ];
    }

    /**
     * Convierte un patrón de ruta a expresión regular.
     * Soporta: {param}, {param:\d+}, {param:[a-z_]+}
     */
    protected static function patronARegex(string $patron): string
    {
        $patron = trim($patron, '/');

        /* 1. Extraer parámetros {param:regex} para protegerlos del escape */
        $placeholders = [];
        $patronConPlaceholders = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}/',
            function ($matches) use (&$placeholders) {
                $nombre = $matches[1];
                $regex = $matches[2] ?? '[^/]+';

                /* Guardar el reemplazo final (el grupo de captura de regex) */
                $placeholder = "__PARAM_" . count($placeholders) . "__";
                $placeholders[$placeholder] = "(?P<{$nombre}>{$regex})";

                return $placeholder;
            },
            $patron
        );

        /* 2. Escapar caracteres especiales de regex en el resto del path */
        $patronEscapado = preg_replace('/[.+^$()[\]|]/', '\\\\$0', $patronConPlaceholders);

        /* 3. Restaurar los parámetros (que contienen regex válido) */
        $regexFinal = str_replace(array_keys($placeholders), array_values($placeholders), $patronEscapado);

        return '#^' . $regexFinal . '$#';
    }

    /**
     * Extrae los nombres de parámetros de un patrón.
     */
    protected static function extraerNombresParams(string $patron): array
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::[^}]+)?\}/', $patron, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Despacha una solicitud CGI.
     */
    public function despachar(CgiRequest $request): CgiResponse
    {
        $path = trim($request->path(), '/');
        $metodo = $request->method();

        foreach (self::$rutas as $ruta) {
            /* Validar método (soportando HEAD como GET implícito) */
            if ($ruta['metodo'] !== $metodo && $ruta['metodo'] !== 'ANY') {
                if ($metodo === 'HEAD' && $ruta['metodo'] === 'GET') {
                    // Permitir HEAD en rutas GET
                } else {
                    continue;
                }
            }

            if (preg_match($ruta['regex'], $path, $matches)) {
                /* Extraer parámetros nombrados */
                $params = [];
                foreach ($ruta['params'] as $nombre) {
                    if (isset($matches[$nombre])) {
                        $params[$nombre] = $matches[$nombre];
                    }
                }

                return $this->ejecutarHandler($ruta, $request, $params);
            }
        }

        /* Ruta no encontrada */
        return $this->respuesta404($request);
    }

    /**
     * Ejecuta el handler de una ruta con sus middlewares.
     */
    protected function ejecutarHandler(
        array $ruta,
        CgiRequest $request,
        array $params
    ): CgiResponse {
        try {
            $handler = $ruta['handler'];
            $middlewares = array_merge(self::$middlewaresGlobales, $ruta['middlewares']);

            /* Construir pipeline de middlewares */
            $pipeline = $this->construirPipeline($middlewares, function ($req) use ($handler, $params) {
                return $this->invocarHandler($handler, $req, $params);
            });

            $response = $pipeline($request);

            return $this->normalizarRespuesta($response);
        } catch (Throwable $e) {
            return $this->manejarExcepcion($e, $request);
        }
    }

    /**
     * Construye el pipeline de middlewares.
     */
    protected function construirPipeline(array $middlewares, Closure $final): Closure
    {
        return array_reduce(
            array_reverse($middlewares),
            function ($siguiente, $middleware) {
                return function ($request) use ($middleware, $siguiente) {
                    $instancia = $this->resolverMiddleware($middleware);

                    if ($instancia && method_exists($instancia, 'process')) {
                        return $instancia->process($request, $siguiente);
                    }

                    return $siguiente($request);
                };
            },
            $final
        );
    }

    /**
     * Resuelve una clase de middleware.
     */
    protected function resolverMiddleware($middleware): ?object
    {
        if (is_object($middleware)) {
            return $middleware;
        }

        if (is_string($middleware) && class_exists($middleware)) {
            return new $middleware();
        }

        if (is_array($middleware) && count($middleware) === 2) {
            [$clase, $args] = $middleware;
            if (class_exists($clase)) {
                return new $clase(...(array)$args);
            }
        }

        return null;
    }

    /**
     * Invoca el handler del controlador.
     */
    protected function invocarHandler($handler, CgiRequest $request, array $params)
    {
        /* Handler es un Closure */
        if ($handler instanceof Closure) {
            return $handler($request, ...array_values($params));
        }

        /* Handler es [Clase, metodo] */
        if (is_array($handler) && count($handler) === 2) {
            [$clase, $metodo] = $handler;

            if (is_string($clase)) {
                $clase = new $clase();
            }

            /* Inyectar request en los parámetros del método */
            $reflection = new ReflectionMethod($clase, $metodo);
            $argumentos = $this->resolverArgumentos($reflection, $request, $params);

            return $clase->$metodo(...$argumentos);
        }

        throw new \RuntimeException('Handler de ruta inválido');
    }

    /**
     * Resuelve los argumentos para un método del controlador.
     */
    protected function resolverArgumentos(
        ReflectionMethod $method,
        CgiRequest $request,
        array $params
    ): array {
        $argumentos = [];

        foreach ($method->getParameters() as $param) {
            $nombre = $param->getName();
            $tipo = $param->getType();

            /* Obtener nombre del tipo de forma segura */
            $tipoNombre = null;
            if ($tipo instanceof \ReflectionNamedType) {
                $tipoNombre = $tipo->getName();
            }

            /* Si es un Request, inyectarlo */
            if (
                $tipoNombre === CgiRequest::class
                || $tipoNombre === 'support\\Request'
                || $tipoNombre === 'Webman\\Http\\Request'
                || $nombre === 'request'
            ) {
                $argumentos[] = $request;
                continue;
            }

            /* Si es un parámetro de ruta */
            if (isset($params[$nombre])) {
                $argumentos[] = $params[$nombre];
                continue;
            }

            /* Si tiene valor por defecto */
            if ($param->isDefaultValueAvailable()) {
                $argumentos[] = $param->getDefaultValue();
                continue;
            }

            /* Si el tipo permite null o no tiene tipo */
            if (!$tipo || ($tipo instanceof \ReflectionNamedType && $tipo->allowsNull())) {
                $argumentos[] = null;
            }
        }

        return $argumentos;
    }

    /**
     * Normaliza la respuesta del handler.
     */
    protected function normalizarRespuesta($response): CgiResponse
    {
        if ($response instanceof CgiResponse) {
            return $response;
        }

        /* Si es un Response de Webman/support */
        if ($response instanceof \support\Response || $response instanceof \Webman\Http\Response) {
            return CgiResponse::desdeWebman($response);
        }

        /* Si es un array, convertir a JSON */
        if (is_array($response)) {
            return CgiResponse::json($response);
        }

        /* Si es string, devolver como HTML */
        if (is_string($response)) {
            return CgiResponse::html($response);
        }

        return new CgiResponse(200, [], (string)$response);
    }

    /**
     * Genera respuesta 404.
     */
    protected function respuesta404(CgiRequest $request): CgiResponse
    {
        if ($request->expectsJson()) {
            return new CgiResponse(
                404,
                ['Content-Type' => 'application/json'],
                json_encode(['success' => false, 'message' => 'Ruta no encontrada'])
            );
        }

        $archivo404 = public_path('404.html');
        if (file_exists($archivo404)) {
            return new CgiResponse(404, [], file_get_contents($archivo404));
        }

        return new CgiResponse(404, [], '<h1>404 - Página no encontrada</h1>');
    }

    /**
     * Maneja excepciones durante la ejecución.
     */
    protected function manejarExcepcion(Throwable $e, CgiRequest $request): CgiResponse
    {
        $debug = config('app.debug', false);

        if ($request->expectsJson()) {
            $data = [
                'success' => false,
                'message' => $debug ? $e->getMessage() : 'Error interno del servidor',
            ];

            if ($debug) {
                $data['exception'] = get_class($e);
                $data['file'] = $e->getFile();
                $data['line'] = $e->getLine();
                $data['trace'] = explode("\n", $e->getTraceAsString());
            }

            return new CgiResponse(
                500,
                ['Content-Type' => 'application/json'],
                json_encode($data)
            );
        }

        if ($debug) {
            $html = sprintf(
                '<h1>Error: %s</h1><pre>%s</pre><h3>Stack Trace:</h3><pre>%s</pre>',
                htmlspecialchars($e->getMessage()),
                htmlspecialchars("{$e->getFile()}:{$e->getLine()}"),
                htmlspecialchars($e->getTraceAsString())
            );
            return new CgiResponse(500, [], $html);
        }

        return new CgiResponse(500, [], '<h1>500 - Error interno del servidor</h1>');
    }

    /**
     * Registra middlewares globales.
     */
    public static function middleware(array $middlewares): void
    {
        self::$middlewaresGlobales = array_merge(self::$middlewaresGlobales, $middlewares);
    }

    /**
     * Limpia todas las rutas (útil para testing).
     */
    public static function limpiar(): void
    {
        self::$rutas = [];
        self::$middlewaresGlobales = [];
        self::$instancia = null;
    }

    /**
     * Obtiene todas las rutas registradas.
     */
    public static function obtenerRutas(): array
    {
        return self::$rutas;
    }

    /**
     * Reemplaza todas las rutas registradas.
     * Usado por CgiRouteShim para aplicar middlewares retroactivamente.
     */
    public static function reemplazarRutas(array $rutas): void
    {
        self::$rutas = $rutas;
    }

    /**
     * Marca el inicio de un grupo de rutas para tracking de middlewares.
     */
    public static function marcarInicioGrupo(): int
    {
        return count(self::$rutas);
    }

    /**
     * Aplica middlewares a rutas desde un índice dado.
     */
    public static function aplicarMiddlewaresDesde(int $indiceInicio, array $middlewares): void
    {
        for ($i = $indiceInicio; $i < count(self::$rutas); $i++) {
            self::$rutas[$i]['middlewares'] = array_merge(
                $middlewares,
                self::$rutas[$i]['middlewares'] ?? []
            );
        }
    }
}
