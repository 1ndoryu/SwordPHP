<?php

/**
 * Componente: Area de Texto (Textarea)
 * 
 * Props:
 * - label: string (opcional)
 * - name: string (requerido)
 * - value: string (opcional)
 * - id: string (opcional)
 * - class: string (opcional)
 * - placeholder: string (opcional)
 * - rows: int (default: 5)
 * - required: bool (default: false)
 * - helpText: string (opcional)
 */

$id = $id ?? 'textarea_' . $name;
$class = $class ?? '';
$value = $value ?? '';
$rows = $rows ?? 5;
$required = $required ?? false;
?>
<div class="grupoFormulario">
    <?php if (!empty($label)): ?>
        <label for="<?= $id ?>" class="etiquetaCampo">
            <?= $label ?>
            <?php if ($required): ?><span class="requerido">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <textarea
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name) ?>"
        class="<?= htmlspecialchars($class) ?: 'textareaFormulario' ?>"
        <?php if (!empty($placeholder)): ?>placeholder="<?= htmlspecialchars($placeholder) ?>" <?php endif; ?>
        rows="<?= $rows ?>"
        <?= $required ? 'required' : '' ?>><?= htmlspecialchars($value) ?></textarea>

    <?php if (!empty($helpText)): ?>
        <span class="ayudaCampo"><?= $helpText ?></span>
    <?php endif; ?>
</div>