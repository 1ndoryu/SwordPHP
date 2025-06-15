
<?php
// Usar los labels del array de configuración para que la vista sea genérica.
$labels = $config['labels'];
include __DIR__ . '/../../layouts/admin-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($labels['edit_item'] ?? 'Editar entrada') ?></h1>
</div>

<form action="/panel/<?= $slug ?>/editar/<?= $entrada->id ?>" method="POST">
    <div class="mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($entrada->titulo) ?>" required>
    </div>
    <div class="mb-3">
        <label for="contenido" class="form-label">Contenido</label>
        <textarea class="form-control" id="contenido" name="contenido" rows="10"><?= htmlspecialchars($entrada->contenido) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

<?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>