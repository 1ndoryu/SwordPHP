<?php
$labels = $config['labels'];
echo partial('layouts/admin-header', []);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($labels['add_new_item'] ?? 'Añadir nuevo') ?></h1>
</div>

<form action="/panel/<?= $slug ?>/crear" method="POST">
    <div class="mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" class="form-control" id="titulo" name="titulo" required>
    </div>
    <div class="mb-3">
        <label for="contenido" class="form-label">Contenido</label>
        <textarea class="form-control" id="contenido" name="contenido" rows="10"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Publicar</button>
</form>

<?php echo partial('layouts/admin-footer', []); ?>