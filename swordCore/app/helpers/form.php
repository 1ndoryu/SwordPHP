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

if (!function_exists('renderFormPlugin')) {
    /**
     * Renderiza un campo de formulario genérico (input, textarea, select, checkbox).
     *
     * @param array $args Argumentos para configurar el campo.
     * - tipo (string): 'text', 'email', 'password', 'number', 'textarea', 'select', 'checkbox'. Requerido.
     * - name (string): El atributo 'name' del campo. Requerido.
     * - id (string): El atributo 'id'. Si se omite, se usa el 'name'.
     * - label (string): El texto para la etiqueta <label>.
     * - value (mixed): El valor actual del campo (para inputs) o el valor seleccionado (para select).
     * - placeholder (string): Placeholder para inputs y textarea.
     * - descripcion (string): Texto de ayuda que se muestra debajo del campo.
     * - opciones (array): Array asociativo ['valor' => 'texto'] para 'select'.
     * - estaMarcado (bool): Para 'checkbox', indica si está marcado.
     * - atributos (array): Array de atributos HTML adicionales.
     *
     * @return string El HTML del campo de formulario.
     */
    function renderFormPlugin(array $args): string
    {
        $defaults = [
            'tipo' => 'text',
            'name' => '',
            'id' => '',
            'label' => '',
            'value' => '',
            'placeholder' => '',
            'descripcion' => '',
            'opciones' => [],
            'estaMarcado' => false,
            'atributos' => [],
        ];
        $args = array_merge($defaults, $args);

        if (empty($args['name'])) return ''; // El nombre es esencial.
        if (empty($args['id'])) $args['id'] = $args['name'];

        $atributosStr = _construirAtributosHtml($args['atributos']);
        $labelHtml = $args['label'] && $args['tipo'] !== 'checkbox' ? sprintf('<label for="%s"><strong>%s</strong></label>', htmlspecialchars($args['id']), htmlspecialchars($args['label'])) : '';
        $descripcionHtml = $args['descripcion'] ? sprintf('<small>%s</small>', htmlspecialchars($args['descripcion'])) : '';
        $campoHtml = '';

        switch ($args['tipo']) {
            case 'textarea':
                $campoHtml = sprintf(
                    '<textarea id="%s" name="%s" placeholder="%s" %s>%s</textarea>',
                    htmlspecialchars($args['id']), htmlspecialchars($args['name']), htmlspecialchars($args['placeholder']), $atributosStr, htmlspecialchars($args['value'])
                );
                break;

            case 'select':
                $opcionesHtml = '';
                foreach ($args['opciones'] as $val => $texto) {
                    $selectedAttr = ((string)$val === (string)$args['value']) ? ' selected' : '';
                    $opcionesHtml .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($val, ENT_QUOTES), $selectedAttr, htmlspecialchars($texto));
                }
                $campoHtml = sprintf('<select id="%s" name="%s" %s>%s</select>', htmlspecialchars($args['id']), htmlspecialchars($args['name']), $atributosStr, $opcionesHtml);
                break;

            case 'checkbox':
                $atributosCheckbox = $args['atributos'];
                if ($args['estaMarcado']) $atributosCheckbox['checked'] = true;
                $atributosCheckboxStr = _construirAtributosHtml($atributosCheckbox);

                $campoHtml = sprintf(
                    '<label for="%s"><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
                    htmlspecialchars($args['id']), htmlspecialchars($args['id']), htmlspecialchars($args['name']), $atributosCheckboxStr, htmlspecialchars($args['label'])
                );
                // Para checkbox, la descripción va fuera del label
                return sprintf('<div class="grupo-formulario">%s%s</div>', $campoHtml, $descripcionHtml);

            default: // text, email, password, number, etc.
                $campoHtml = sprintf(
                    '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" %s>',
                    htmlspecialchars($args['tipo']), htmlspecialchars($args['id']), htmlspecialchars($args['name']), htmlspecialchars($args['value'], ENT_QUOTES), htmlspecialchars($args['placeholder']), $atributosStr
                );
                break;
        }
        
        return sprintf('<div class="grupo-formulario">%s%s%s</div>', $labelHtml, $campoHtml, $descripcionHtml);
    }
}