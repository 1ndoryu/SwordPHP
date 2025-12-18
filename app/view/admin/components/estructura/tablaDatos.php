<?php

/**
 * Componente: Tabla de Datos
 * 
 * Props:
 * - columnas: array (titulos de cabecera)
 * - filas: array (array de arrays con los datos de las celdas. Pueden ser HTML)
 * - id: string (opcional)
 * - class: string (opcional)
 * - mensajeVacio: string (opcional)
 */

$id = $id ?? 'tablaDatos';
$class = $class ?? '';
$filas = $filas ?? [];
$columnas = $columnas ?? [];
?>
<div class="contenedorTabla">
    <table class="tablaContenidos <?= htmlspecialchars($class) ?>" id="<?= htmlspecialchars($id) ?>">
        <thead>
            <tr>
                <?php foreach ($columnas as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (count($filas) > 0): ?>
                <?php foreach ($filas as $fila): ?>
                    <tr>
                        <?php foreach ($fila as $celda): ?>
                            <td><?= $celda ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= count($columnas) ?>" class="celdaVacia">
                        <div class="mensajeVacio">
                            <p><?= htmlspecialchars($mensajeVacio ?? 'No hay datos') ?></p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>