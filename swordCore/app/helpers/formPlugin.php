<?php

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
        $labelHtml = $args['label'] && $args['tipo'] !== 'checkbox' ? sprintf('<label for="%s"><label>%s</label></label>', htmlspecialchars($args['id']), htmlspecialchars($args['label'])) : '';
        $descripcionHtml = $args['descripcion'] ? sprintf('<small>%s</small>', htmlspecialchars($args['descripcion'])) : '';
        $campoHtml = '';

        switch ($args['tipo']) {
            case 'textarea':
                $campoHtml = sprintf(
                    '<textarea id="%s" name="%s" placeholder="%s" %s>%s</textarea>',
                    htmlspecialchars($args['id']),
                    htmlspecialchars($args['name']),
                    htmlspecialchars($args['placeholder']),
                    $atributosStr,
                    htmlspecialchars($args['value'])
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
                    '<label class="checkboxPluginForm" for="%s"><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
                    htmlspecialchars($args['id']),
                    htmlspecialchars($args['id']),
                    htmlspecialchars($args['name']),
                    $atributosCheckboxStr,
                    htmlspecialchars($args['label'])
                );
                // Para checkbox, la descripción va fuera del label
                return sprintf('<div class="grupo-formulario">%s%s</div>', $campoHtml, $descripcionHtml);

            default: // text, email, password, number, etc.
                $campoHtml = sprintf(
                    '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" %s>',
                    htmlspecialchars($args['tipo']),
                    htmlspecialchars($args['id']),
                    htmlspecialchars($args['name']),
                    htmlspecialchars($args['value'], ENT_QUOTES),
                    htmlspecialchars($args['placeholder']),
                    $atributosStr
                );
                break;
        }

        return sprintf('<div class="grupo-formulario">%s%s%s</div>', $labelHtml, $campoHtml, $descripcionHtml);
    }
}

if (!function_exists('renderizarFormularioAjustesPlugin')) {
    /**
     * Renderiza un formulario de ajustes completo para un plugin.
     *
     * @param array $args Argumentos para configurar el formulario.
     * - 'action' (string): El atributo 'action' del formulario. Por defecto, la URL actual.
     * - 'method' (string): El método del formulario. Por defecto, 'POST'.
     * - 'campos' (array): Array de arrays, donde cada subarray define un campo usando los argumentos de `renderFormPlugin`.
     * - 'mensajeExito' (string): Un mensaje de éxito opcional para mostrar al inicio del formulario.
     * - 'descripcionFormulario' (string): Un texto descriptivo opcional para mostrar antes de los campos.
     * - 'textoBoton' (string): El texto para el botón de envío. Por defecto, 'Guardar Cambios'.
     *
     * @return string El HTML del formulario completo.
     */
    function renderizarFormularioAjustesPlugin(array $args): string
    {
        $defaults = [
            'action' => '',
            'method' => 'POST',
            'campos' => [],
            'mensajeExito' => '',
            'descripcionFormulario' => '',
            'textoBoton' => 'Guardar Cambios',
        ];
        $args = array_merge($defaults, $args);

        $htmlCampos = '';
        if (!empty($args['campos'])) {
            foreach ($args['campos'] as $campo) {
                $htmlCampos .= renderFormPlugin($campo);
            }
        }

        $htmlMensajeExito = '';
        if (!empty($args['mensajeExito'])) {
            $htmlMensajeExito = sprintf(
                '<div class="alerta alertaExito" style="margin-bottom: 1rem;">%s</div>',
                htmlspecialchars($args['mensajeExito'])
            );
        }

        $htmlDescripcion = '';
        if (!empty($args['descripcionFormulario'])) {
            $htmlDescripcion = sprintf(
                '<p>%s</p><hr>',
                htmlspecialchars($args['descripcionFormulario'])
            );
        }

        return sprintf(
            '<div class="pluginSw_formulario-contenedor" style="flex: 1; max-width: none;">
            <div class="pluginSw_cuerpo-formulario">
                %s
                %s
                <form method="%s" action="%s">
                    %s
                    %s
                    <div class="pluginSw_pie-formulario" style="justify-content: flex-start;">
                        <button type="submit" class="btnN">%s</button>
                    </div>
                </form>
            </div>
        </div>',
            $htmlMensajeExito,
            $htmlDescripcion,
            htmlspecialchars(strtoupper($args['method'])),
            htmlspecialchars($args['action']),
            csrf_field(),
            $htmlCampos,
            htmlspecialchars($args['textoBoton'])
        );
    }
}
