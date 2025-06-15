
<?php
// Usar los labels del array de configuración para que la vista sea genérica.
$labels = $config['labels'];
include __DIR__ . '/../../layouts/admin-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($labels['name'] ?? 'Contenidos') ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/panel/<?= $slug ?>/crear" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars($labels['add_new_item'] ?? 'Añadir nuevo') ?></a>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Título</th>
                <th scope="col">Slug</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entradas as $entrada) : ?>
                <tr>
                    <td><?= $entrada->id ?></td>
                    <td><?= htmlspecialchars($entrada->titulo) ?></td>
                    <td><?= htmlspecialchars($entrada->slug) ?></td>
                    <td>
                        <a href="/panel/<?= $slug ?>/editar/<?= $entrada->id ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="/panel/<?= $slug ?>/eliminar/<?= $entrada->id ?>" method="POST" style="display: inline-block;">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar esta entrada?');">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>