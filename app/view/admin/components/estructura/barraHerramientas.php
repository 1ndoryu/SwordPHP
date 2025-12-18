<?php

/**
 * Componente: Barra de Herramientas
 * 
 * Props:
 * - izquierda: string (HTML para zona izquierda)
 * - derecha: string (HTML para zona derecha)
 * - class: string (extra)
 */

$class = $class ?? '';
?>
<div class="barraHerramientas <?= htmlspecialchars($class) ?>">
    <div class="barraHerramientasIzquierda">
        <?= $izquierda ?? '' ?>
    </div>
    <div class="barraHerramientasDerecha">
        <?= $derecha ?? '' ?>
    </div>
</div>