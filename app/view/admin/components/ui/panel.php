<?php

/**
 * Componente: Panel
 * 
 * Props:
 * - title: string (opcional)
 * - content: string (HTML content)
 * - class: string (opcional, clases extra para el contenedor)
 * - id: string (opcional)
 * - footer: string (opcional, HTML footer)
 */

$class = $class ?? '';
$id = $id ?? '';
?>
<div class="panelLateral <?= htmlspecialchars($class) ?>" <?php if ($id): ?>id="<?= htmlspecialchars($id) ?>" <?php endif; ?>>
    <?php if (!empty($title)): ?>
        <h3 class="tituloPanelLateral"><?= htmlspecialchars($title) ?></h3>
    <?php endif; ?>

    <div class="contenidoPanelLateral">
        <?= $content ?? '' ?>
    </div>

    <?php if (!empty($footer)): ?>
        <div class="piePanelLateral">
            <?= $footer ?>
        </div>
    <?php endif; ?>
</div>