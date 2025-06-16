<?php
/**
 * Componente para los campos secundarios del formulario (ej. Estado).
 *
 * @param string $estadoActual El valor actual del campo estado. Por defecto 'borrador'.
 * @param string $idEntrada (Opcional) Usado para generar IDs únicos para los campos si es necesario.
 */

$estadoActual = $estadoActual ?? 'borrador';
?>

<div class="grupo-formulario estado">
    <label for="estado">Estado</label>
    <select id="estado" name="estado">
        <option value="borrador" <?php echo $estadoActual === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
        <option value="publicado" <?php echo $estadoActual === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
    </select>
</div>

<?php
// Aquí se podrían añadir otros campos que vayan en el contenedor secundario en el futuro si es necesario.
// Por ejemplo, campos para 'fecha de publicación', 'visibilidad', etc.
?>
```
