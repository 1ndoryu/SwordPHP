<?php
/**
 * Componente para los campos principales del formulario (Título, Subtítulo, Contenido).
 *
 * @param string $tituloValor El valor actual del campo título.
 * @param string $subtituloValor (Opcional) El valor actual del campo subtítulo.
 * @param string $contenidoValor El valor actual del campo contenido.
 * @param bool $incluirSubtitulo Define si el campo subtítulo debe mostrarse. Por defecto es false.
 * @param string $idEntrada (Opcional) Usado para generar IDs únicos para los campos si es necesario, aunque no se usa activamente en este snippet.
 */

$tituloValor = $tituloValor ?? '';
$subtituloValor = $subtituloValor ?? '';
$contenidoValor = $contenidoValor ?? '';
$incluirSubtitulo = $incluirSubtitulo ?? false;

?>

<div class="grupo-formulario">
    <label for="titulo">Título</label>
    <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars($tituloValor); ?>" required>
</div>

<?php if ($incluirSubtitulo): ?>
<div class="grupo-formulario">
    <label for="subtitulo">Subtítulo (Opcional)</label>
    <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars($subtituloValor); ?>">
</div>
<?php endif; ?>

<div class="grupo-formulario">
    <label for="contenido">Contenido</label>
    <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido aquí..."><?php echo htmlspecialchars($contenidoValor); ?></textarea>
</div>

<?php
// La gestión de metadatos se mantiene separada ya que es un componente existente y su lógica de datos es más compleja.
// Se pasará directamente al formularioGeneral o se incluirá en la vista que lo llama.
// echo partial('admin/components/gestor-metadatos', ['metadatos' => $metadatos ?? []]);
?>
```
