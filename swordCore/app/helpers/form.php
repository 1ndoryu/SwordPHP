<?php

if (!function_exists('csrf_token')) {
    /**
     * Obtiene el valor del token CSRF actual.
     *
     * @return string
     */
    function csrf_token()
    {
        return session('_token', '');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Genera un campo de formulario input hidden con el token CSRF.
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Recupera los datos de "input antiguo" (old input) de la sesión.
     *
     * @param  string|null  $key La clave específica del input a recuperar.
     * @param  mixed  $default El valor por defecto si no se encuentra el input antiguo.
     * @return mixed
     */
    function old($key = null, $default = null)
    {
        $old_input = session('_old_input');

        if (is_null($old_input)) {
            return $default;
        }

        if (!is_null($key)) {
            return $old_input[$key] ?? $default;
        }

        return $old_input;
    }
}

if (!function_exists('_construirAtributosHtml')) {
    /**
     * Helper interno para construir una cadena de atributos HTML a partir de un array.
     * @param array $atributos
     * @return string
     * @internal
     */
    function _construirAtributosHtml(array $atributos): string
    {
        $html = [];
        foreach ($atributos as $key => $value) {
            if ($value === true) {
                $html[] = htmlspecialchars($key);
            } elseif ($value !== false && $value !== null && $value !== '') {
                $html[] = sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        return implode(' ', $html);
    }
}


