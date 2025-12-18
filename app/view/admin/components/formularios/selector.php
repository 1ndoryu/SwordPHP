<?php

/**
 * Componente: Selector (Select)
 * 
 * Props:
 * - label: string (opcional)
 * - name: string (requerido)
 * - value: string (opcional, valor seleccionado actual)
 * - options: array (requerido, formato: ['valor' => 'Etiqueta'] o [['value' => 'v', 'label' => 'l']])
 * - id: string (opcional)
 * - class: string (opcional)
 * - required: bool (default: false)
 * - onchange: string (opcional, JS code)
 * - emptyOption: string (opcional, texto para opcion vacia inicial)
 */

$id = $id ?? 'select_' . $name;
$class = $class ?? '';
$value = $value ?? '';
$required = $required ?? false;
$options = $options ?? [];
?>
<div class="grupoFormulario">
    <?php if (!empty($label)): ?>
        <label for="<?= $id ?>" class="etiquetaCampo">
            <?= $label ?>
            <?php if ($required): ?><span class="requerido">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <select
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name) ?>"
        class="<?= htmlspecialchars($class) ?: 'selectFormulario' ?>"
        <?= $required ? 'required' : '' ?>
        <?php if (!empty($onchange)): ?>onchange="<?= $onchange ?>" <?php endif; ?>>
        <?php if (isset($emptyOption)): ?>
            <option value=""><?= htmlspecialchars($emptyOption) ?></option>
        <?php endif; ?>

        <?php foreach ($options as $optValue => $optLabel): ?>
            <?php
            // Soporte para array simple ['val' => 'label'] o array de arrays [['value'=>'v', 'label'=>'l']]
            $val = is_array($optLabel) ? $optLabel['value'] : $optValue;
            $txt = is_array($optLabel) ? $optLabel['label'] : $optLabel;
            $selected = (string)$val === (string)$value ? 'selected' : '';
            ?>
            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                <?= htmlspecialchars($txt) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>