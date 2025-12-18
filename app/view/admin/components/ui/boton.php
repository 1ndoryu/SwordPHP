<?php

/**
 * Componente: Boton
 * 
 * Props:
 * - text: string
 * - type: string ('button', 'submit', 'reset') o 'link' (a)
 * - variant: string ('primary', 'secondary', 'danger', 'icon') -> mapea a botonPrimario, etc.
 * - href: string (si type == 'link')
 * - onclick: string
 * - icon: string (emoji o html icono)
 * - id: string
 * - class: string (extra)
 * - title: string
 * - disabled: bool
 * - attributes: string (otros atributos raw)
 */

$type = $type ?? 'button';
$variant = $variant ?? 'primary'; // primary, secondary, danger
$text = $text ?? '';
$class = $class ?? '';
$id = $id ?? '';
$disabled = $disabled ?? false;
$attributes = $attributes ?? ''; // data-x="y" etc

// Mapear variante a clase
$btnClass = match ($variant) {
    'primary' => 'botonPrimario',
    'secondary' => 'botonSecundario',
    'danger' => 'botonPeligro',
    'icon' => 'botonIcono',
    default => 'botonPrimario'
};

$fullClass = $btnClass . ' ' . $class;
?>

<?php if ($type === 'link'): ?>
    <a href="<?= htmlspecialchars($href ?? '#') ?>"
        class="<?= htmlspecialchars($fullClass) ?>"
        <?php if ($id): ?>id="<?= htmlspecialchars($id) ?>" <?php endif; ?>
        <?php if (!empty($title)): ?>title="<?= htmlspecialchars($title) ?>" <?php endif; ?>
        <?= $attributes ?>>
        <?php if (!empty($icon)): ?><span class="iconoBoton"><?= $icon ?></span><?php endif; ?>
        <?= htmlspecialchars($text) ?>
    </a>
<?php else: ?>
    <button
        type="<?= htmlspecialchars($type) ?>"
        class="<?= htmlspecialchars($fullClass) ?>"
        <?php if ($id): ?>id="<?= htmlspecialchars($id) ?>" <?php endif; ?>
        <?php if (!empty($onclick)): ?>onclick="<?= $onclick ?>" <?php endif; ?>
        <?php if (!empty($title)): ?>title="<?= htmlspecialchars($title) ?>" <?php endif; ?>
        <?= $disabled ? 'disabled' : '' ?>
        <?= $attributes ?>>
        <?php if (!empty($icon)): ?><span class="iconoBoton"><?= $icon ?></span><?php endif; ?>
        <?= htmlspecialchars($text) ?>
    </button>
<?php endif; ?>