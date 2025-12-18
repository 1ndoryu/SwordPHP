<?php

/**
 * Componente: Campo de Texto (Input)
 * 
 * Props:
 * - label: string (opcional)
 * - name: string (requerido)
 * - value: string (opcional)
 * - type: string (default: 'text')
 * - id: string (opcional, default: genera uno basado en name)
 * - class: string (opcional)
 * - placeholder: string (opcional)
 * - required: bool (default: false)
 * - readonly: bool (default: false)
 * - helpText: string (opcional)
 * - error: string (opcional)
 */

$type = $type ?? 'text';
$id = $id ?? 'input_' . $name;
$class = $class ?? ''; // Clase base sugerida: 'inputFormulario' si no se pasa nada, pero dejemos vacio para flexibilidad
$ifClass = empty($class) ? 'inputFormulario' : $class; // O podemos forzar una clase base
// En el codigo original usan 'inputTitulo', 'inputSlug', etc. Mejor dejar que pasen la clase.
// Pero para estandarizacion, podriamos poner una clase por defecto si no se especifica.
// Asumiremos que el CSS maneja inputs genericos o que se pasaran clases especificas.
// Revisando editor.php, usan clases especificas.
// Sin embargo, para futuros usos, 'inputFormulario' seria bueno.

$value = $value ?? '';
$required = $required ?? false;
$readonly = $readonly ?? false;
?>
<div class="grupoFormulario <?= !empty($error) ? 'conError' : '' ?>">
    <?php if (!empty($label)): ?>
        <label for="<?= $id ?>" class="etiquetaCampo">
            <?= $label ?>
            <?php if ($required): ?><span class="requerido">*</span><?php endif; ?>
            <?php if (!empty($helpText)): ?>
                <span class="ayudaCampo"><?= $helpText ?></span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="contenedorInput" style="display: flex; align-items: center;">
        <?php if (!empty($prefix)): ?>
            <span class="prefijoInput"><?= $prefix ?></span>
        <?php endif; ?>

        <input
            type="<?= htmlspecialchars($type) ?>"
            id="<?= htmlspecialchars($id) ?>"
            name="<?= htmlspecialchars($name) ?>"
            value="<?= htmlspecialchars($value) ?>"
            class="<?= htmlspecialchars($class) ?: 'inputFormulario' ?>"
            <?php if (!empty($placeholder)): ?>placeholder="<?= htmlspecialchars($placeholder) ?>" <?php endif; ?>
            <?= $required ? 'required' : '' ?>
            <?= $readonly ? 'readonly' : '' ?>
            style="flex: 1;">

        <?php if (!empty($suffix)): ?>
            <span class="sufijoInput"><?= $suffix ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <span class="mensajeError"><?= htmlspecialchars($error) ?></span>
    <?php endif; ?>
</div>