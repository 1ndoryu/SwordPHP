<?php

/**
 * Componente: Alerta
 * 
 * Props:
 * - message: string
 * - type: string ('success', 'error', 'warning', 'info')
 * - dismissible: bool (default: false)
 */

$type = $type ?? 'info';
$classType = match ($type) {
    'success' => 'alertaExito',
    'error' => 'alertaError',
    'warning' => 'alertaAdvertencia',
    default => 'alertaInfo'
};
?>
<?php if (!empty($message)): ?>
    <div class="<?= $classType ?>">
        <?= $message ?>
        <?php if (!empty($dismissible) && $dismissible): ?>
            <button type="button" class="btnCerrarAlerta" onclick="this.parentElement.remove()">×</button>
        <?php endif; ?>
    </div>
<?php endif; ?>