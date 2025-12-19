<?php

namespace app\support;

/**
 * Clase de Response para modo CGI.
 * Proporciona una interfaz compatible con Webman\Http\Response
 * pero envía la respuesta directamente al cliente via output buffering.
 */
class CgiResponse
{
    protected int $status = 200;
    protected array $headers = [];
    protected string $body = '';
    protected array $cookies = [];

    /* Mensajes HTTP estándar */
    protected static array $mensajesHttp = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    public function __construct(int $status = 200, array $headers = [], string $body = '')
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Crea una respuesta desde un Response de Webman.
     */
    public static function desdeWebman($response): self
    {
        if ($response instanceof self) {
            return $response;
        }

        $instance = new self();
        $instance->status = $response->getStatusCode();
        $instance->body = $response->rawBody();

        foreach ($response->getHeaders() as $name => $value) {
            $instance->headers[$name] = $value;
        }

        return $instance;
    }

    /**
     * Establece el código de estado HTTP.
     */
    public function withStatus(int $status, string $reason = ''): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Establece un header.
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Añade múltiples headers.
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * Establece el cuerpo de la respuesta.
     */
    public function withBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Establece una cookie.
     */
    public function cookie(
        string $name,
        string $value = '',
        int $maxAge = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'maxAge' => $maxAge,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
            'sameSite' => $sameSite,
        ];
        return $this;
    }

    /**
     * Obtiene el código de estado.
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Obtiene los headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Obtiene un header específico.
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Obtiene el cuerpo de la respuesta.
     */
    public function rawBody(): string
    {
        return $this->body;
    }

    /**
     * Envía la respuesta al cliente.
     */
    public function enviar(): void
    {
        if (headers_sent()) {
            echo $this->body;
            return;
        }

        /* Enviar código de estado */
        $mensaje = self::$mensajesHttp[$this->status] ?? 'Unknown';
        $protocolo = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        header("{$protocolo} {$this->status} {$mensaje}");

        /* Enviar headers */
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        /* Enviar cookies */
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                [
                    'expires' => $cookie['maxAge'] > 0 ? time() + $cookie['maxAge'] : 0,
                    'path' => $cookie['path'],
                    'domain' => $cookie['domain'],
                    'secure' => $cookie['secure'],
                    'httponly' => $cookie['httpOnly'],
                    'samesite' => $cookie['sameSite'],
                ]
            );
        }

        /* Enviar cuerpo */
        echo $this->body;
    }

    /**
     * Crea una respuesta JSON.
     */
    public static function json($data, int $options = JSON_UNESCAPED_UNICODE): self
    {
        return new self(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data, $options)
        );
    }

    /**
     * Crea una respuesta de redirección.
     */
    public static function redirect(string $url, int $status = 302): self
    {
        return new self($status, ['Location' => $url], '');
    }

    /**
     * Crea una respuesta HTML.
     */
    public static function html(string $html, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'text/html; charset=utf-8'], $html);
    }

    /**
     * Crea una respuesta de archivo.
     */
    public static function file(string $path): self
    {
        if (!file_exists($path)) {
            return new self(404, [], 'File not found');
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $contenido = file_get_contents($path);

        return new self(200, ['Content-Type' => $mime], $contenido);
    }
}
