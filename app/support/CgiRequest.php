<?php

namespace app\support;

/**
 * Wrapper de Request para modo CGI.
 * 
 * Proporciona una interfaz compatible con Webman\Http\Request
 * pero obtiene los datos de $_GET, $_POST, $_SERVER, etc.
 * 
 * En modo CGI, esta clase reemplaza a support\Request mediante
 * class_alias en public/index.php.
 */
class CgiRequest
{
    protected array $getData;
    protected array $postData;
    protected array $serverData;
    protected array $headersData;
    protected array $cookiesData;
    protected array $filesData;
    protected ?string $rawBodyData = null;
    protected ?array $parsedBody = null;
    protected ?CgiSession $sessionInstance = null;

    public function __construct()
    {
        $this->getData = $_GET;
        $this->postData = $_POST;
        $this->serverData = $_SERVER;
        $this->cookiesData = $_COOKIE;
        $this->filesData = $this->normalizarArchivos($_FILES);
        $this->headersData = $this->extraerHeaders();
    }

    /**
     * Normaliza el array $_FILES para formato consistente.
     */
    protected function normalizarArchivos(array $files): array
    {
        $normalizados = [];

        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $normalizados[$key] = [];
                foreach ($file['name'] as $i => $name) {
                    $normalizados[$key][$i] = [
                        'name' => $name,
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i],
                    ];
                }
            } else {
                $normalizados[$key] = $file;
            }
        }

        return $normalizados;
    }

    /**
     * Extrae headers HTTP de $_SERVER.
     */
    protected function extraerHeaders(): array
    {
        $headers = [];

        foreach ($this->serverData as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($headerName)] = $value;
            }
        }

        if (isset($this->serverData['CONTENT_TYPE'])) {
            $headers['content-type'] = $this->serverData['CONTENT_TYPE'];
        }

        if (isset($this->serverData['CONTENT_LENGTH'])) {
            $headers['content-length'] = $this->serverData['CONTENT_LENGTH'];
        }

        return $headers;
    }

    /**
     * Obtiene la ruta de la solicitud.
     */
    public function path(): string
    {
        $uri = $this->serverData['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        return $path;
    }

    /**
     * Obtiene el método HTTP.
     */
    public function method(): string
    {
        $method = $this->serverData['REQUEST_METHOD'] ?? 'GET';

        /* Soporte para _method override en formularios */
        if ($method === 'POST' && isset($this->postData['_method'])) {
            $method = strtoupper($this->postData['_method']);
        }

        return $method;
    }

    /**
     * Obtiene un parámetro GET.
     */
    public function get(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->getData;
        }

        return $this->getData[$name] ?? $default;
    }

    /**
     * Obtiene un parámetro POST.
     */
    public function post(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->postData;
        }

        return $this->postData[$name] ?? $default;
    }

    /**
     * Obtiene cualquier parámetro (GET o POST).
     */
    public function input(?string $name = null, mixed $default = null): mixed
    {
        $all = array_merge($this->getData, $this->postData);

        if ($name === null) {
            return $all;
        }

        return $all[$name] ?? $default;
    }

    /**
     * Obtiene todos los parámetros.
     */
    public function all(): mixed
    {
        return array_merge($this->getData, $this->postData);
    }

    /**
     * Obtiene solo los parámetros especificados.
     */
    public function only(array $keys): array
    {
        $result = [];
        $all = $this->all();

        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $result[$key] = $all[$key];
            }
        }

        return $result;
    }

    /**
     * Obtiene todos los parámetros excepto los especificados.
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Obtiene un header.
     */
    public function header(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->headersData;
        }

        return $this->headersData[strtolower($name)] ?? $default;
    }

    /**
     * Obtiene una cookie.
     */
    public function cookie(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->cookiesData;
        }

        return $this->cookiesData[$name] ?? $default;
    }

    /**
     * Obtiene un archivo subido.
     * @return array|null
     */
    public function file(?string $name = null): array|null
    {
        if ($name === null) {
            return $this->filesData;
        }

        return $this->filesData[$name] ?? null;
    }

    /**
     * Obtiene el cuerpo raw de la solicitud.
     */
    public function rawBody(): string
    {
        if ($this->rawBodyData === null) {
            $this->rawBodyData = file_get_contents('php://input') ?: '';
        }

        return $this->rawBodyData;
    }

    /**
     * Obtiene la sesión.
     */
    public function session(): CgiSession
    {
        if ($this->sessionInstance === null) {
            $this->sessionInstance = new CgiSession();
        }

        return $this->sessionInstance;
    }

    /**
     * Verifica si es una solicitud AJAX.
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('x-requested-with', '')) === 'xmlhttprequest';
    }

    /**
     * Verifica si espera respuesta JSON.
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('accept', '');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    /**
     * Obtiene la URL completa.
     */
    public function url(): string
    {
        $scheme = $this->isHttps() ? 'https' : 'http';
        $host = $this->host();
        $path = $this->path();

        return "{$scheme}://{$host}{$path}";
    }

    /**
     * Obtiene la URL completa con query string.
     */
    public function fullUrl(): string
    {
        $url = $this->url();
        $query = $this->serverData['QUERY_STRING'] ?? '';

        return $query ? "{$url}?{$query}" : $url;
    }

    /**
     * Obtiene el host.
     */
    public function host(bool $withoutPort = false): ?string
    {
        $host = $this->serverData['HTTP_HOST'] ?? $this->serverData['SERVER_NAME'] ?? 'localhost';
        if ($withoutPort) {
            return preg_replace('/:\d+$/', '', $host);
        }
        return $host;
    }

    /**
     * Verifica si es HTTPS.
     */
    public function isHttps(): bool
    {
        return ($this->serverData['HTTPS'] ?? '') === 'on'
            || ($this->serverData['SERVER_PORT'] ?? 80) == 443
            || ($this->header('x-forwarded-proto') === 'https');
    }

    /**
     * Obtiene la IP del cliente.
     */
    public function getRemoteIp(): string
    {
        return $this->header('x-forwarded-for')
            ?? $this->header('x-real-ip')
            ?? $this->serverData['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    /**
     * Alias para getRemoteIp().
     */
    public function getRealIp(bool $safeMode = true): string
    {
        return $this->getRemoteIp();
    }
}
