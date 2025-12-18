<?php

/**
 * Componente: Casilla de Verificacion (Checkbox)
 * 
 * Props:
 * - label: string (texto junto al checkbox)
 * - name: string (requerido)
 * - value: string (valor cuando esta checkeado, default '1')
 * - checked: bool (default: false)
 * - id: string (opcional)
 * - class: string (opcional)
 */

$id = $id ?? 'checkbox_' . $name . '_' . rand(100, 999);
$class = $class ?? '';
$value = $value ?? '1';
$checked = $checked ?? false;
?>
<div class="grupoCheckbox <?= htmlspecialchars($class) ?>">
    <input
        type="checkbox"
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name) ?>"
        value="<?= htmlspecialchars($value) ?>"
        <?= $checked ? 'checked' : '' ?>>
    <label for="<?= htmlspecialchars($id) ?>">
        <?= $label ?>
    </label>
</div>