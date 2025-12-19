<?php

/**
 * SwordPHP - Helpers CGI
 * 
 * Funciones auxiliares que replican los helpers de Webman
 * para garantizar compatibilidad en modo CGI.
 */

/* 
 * Nota: La mayoría de helpers ya están definidos en:
 * vendor/workerman/webman-framework/src/support/helpers.php
 * 
 * Este archivo solo añade funciones específicas para el modo CGI
 * que no están cubiertas por el framework.
 */

if (!function_exists('esWebman')) {
    /**
     * Verifica si estamos en modo Webman.
     */
    function esWebman(): bool
    {
        return \app\support\Environment::esWebman();
    }
}

if (!function_exists('esCgi')) {
    /**
     * Verifica si estamos en modo CGI.
     */
    function esCgi(): bool
    {
        return \app\support\Environment::esCgi();
    }
}

if (!function_exists('esAjax')) {
    /**
     * Verifica si la solicitud actual es AJAX.
     */
    function esAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }
}

if (!function_exists('esperaJson')) {
    /**
     * Verifica si el cliente espera respuesta JSON.
     */
    function esperaJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json') || esAjax();
    }
}

if (!function_exists('obtenerIpCliente')) {
    /**
     * Obtiene la IP real del cliente.
     */
    function obtenerIpCliente(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '127.0.0.1';
    }
}

if (!function_exists('urlActual')) {
    /**
     * Obtiene la URL actual completa.
     */
    function urlActual(): string
    {
        $scheme = (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? 80) == 443)
            ? 'https'
            : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return "{$scheme}://{$host}{$uri}";
    }
}

if (!function_exists('url')) {
    /**
     * Genera una URL absoluta.
     */
    function url(string $path = ''): string
    {
        $scheme = (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? 80) == 443)
            ? 'https'
            : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return "{$scheme}://{$host}/" . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Genera URL para un asset estático.
     */
    function asset(string $path): string
    {
        return url($path);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Genera o recupera un token CSRF.
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Genera un campo hidden con el token CSRF.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Genera un campo hidden para override del método HTTP.
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Recupera un valor del flash de la sesión anterior.
     */
    function old(string $key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    /**
     * Almacena un mensaje flash en la sesión.
     */
    function flash(string $key, $value): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('get_flash')) {
    /**
     * Recupera y elimina un mensaje flash.
     */
    function get_flash(string $key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}

if (!function_exists('abort')) {
    /**
     * Termina la ejecución con un código de error HTTP.
     */
    function abort(int $code, string $message = ''): void
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ];

        $statusMessage = $message ?: ($messages[$code] ?? 'Error');

        http_response_code($code);

        if (esperaJson()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $statusMessage]);
        } else {
            echo "<h1>{$code} - {$statusMessage}</h1>";
        }

        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirige a la página anterior.
     */
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }
}

if (!function_exists('env')) {
    /**
     * Obtiene una variable de entorno.
     * Esta función ya existe en el autoload de Webman, pero la definimos
     * por si acaso para modo CGI puro.
     */
    if (!function_exists('env')) {
        function env(string $key, $default = null)
        {
            $value = getenv($key);

            if ($value === false) {
                return $default;
            }

            /* Convertir valores especiales */
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
                case 'empty':
                case '(empty)':
                    return '';
            }

            return $value;
        }
    }
}
