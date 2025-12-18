<?php

/**
 * Componente: Etiqueta (Badge)
 * 
 * Props:
 * - text: string
 * - type: string (opcional: 'primary', 'secondary', 'success', 'danger', 'warning', 'info')
 * - class: string (opcional)
 */

$type = $type ?? 'default';
$class = $class ?? '';

// Mapeo simple de tipos a clases si es necesario, o uso directo
// Asumimos que existen clases como .etiquetaSuccess, etc.
// O usamos el sistema existente: etiquetaTipo + ucfirst($type)
$classType = 'etiqueta' . ucfirst($type);
?>
<span class="etiquetaBase <?= htmlspecialchars($classType) ?> <?= htmlspecialchars($class) ?>">
    <?= htmlspecialchars($text) ?>
</span>