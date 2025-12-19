<?php

namespace app\support;

/**
 * Wrapper de Session para modo CGI.
 * Proporciona una interfaz compatible con las sesiones de Webman
 * pero usa las sesiones nativas de PHP.
 */
class CgiSession
{
    protected bool $iniciada = false;

    public function __construct()
    {
        $this->iniciar();
    }

    /**
     * Inicia la sesión si no está iniciada.
     */
    protected function iniciar(): void
    {
        if ($this->iniciada) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->iniciada = true;
    }

    /**
     * Obtiene un valor de la sesión.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Establece un valor en la sesión.
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Establece múltiples valores.
     */
    public function put(array $data): void
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Verifica si existe una clave.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Elimina una clave de la sesión.
     */
    public function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Alias de delete().
     */
    public function forget(string $key): void
    {
        $this->delete($key);
    }

    /**
     * Obtiene todos los datos de la sesión.
     */
    public function all(): array
    {
        return $_SESSION ?? [];
    }

    /**
     * Limpia toda la sesión.
     */
    public function flush(): void
    {
        $_SESSION = [];
    }

    /**
     * Destruye la sesión completamente.
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->iniciada = false;
    }

    /**
     * Regenera el ID de sesión.
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Obtiene el ID de sesión.
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Establece el ID de sesión (debe llamarse antes de iniciar).
     */
    public function setId(string $id): void
    {
        session_id($id);
    }
}
