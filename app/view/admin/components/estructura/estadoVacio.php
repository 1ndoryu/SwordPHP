<?php

/**
 * Componente: Estado Vacio
 * 
 * Props:
 * - mensaje: string
 * - icono: string (opcional, default '📂')
 * - accion: array (opcional, ['texto' => '...', 'url' => '...', 'onclick' => '...'])
 * - class: string (extra)
 */

$icono = $icono ?? '📂';
$class = $class ?? '';
?>
<div class="mensajeVacio <?= htmlspecialchars($class) ?>">
    <?php if ($icono): ?>
        <div class="iconoVacio" style="font-size: 3em; margin-bottom: 1rem;"><?= $icono ?></div>
    <?php endif; ?>

    <p><?= htmlspecialchars($mensaje) ?></p>

    <?php if (!empty($accion)): ?>
        <?php
        $btnUrl = $accion['url'] ?? '#';
        $btnOnclick = $accion['onclick'] ?? '';
        $btnTexto = $accion['texto'] ?? 'Accion';
        ?>
        <?php if (!empty($btnUrl) && $btnUrl !== '#'): ?>
            <a href="<?= htmlspecialchars($btnUrl) ?>" class="botonPrimario">
                <?= htmlspecialchars($btnTexto) ?>
            </a>
        <?php elseif (!empty($btnOnclick)): ?>
            <button type="button" class="botonPrimario" onclick="<?= htmlspecialchars($btnOnclick) ?>">
                <?= htmlspecialchars($btnTexto) ?>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>