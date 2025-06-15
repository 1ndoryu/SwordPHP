<?php
// 1. Define el título que usará el admin-header.php
// La variable $titulo es pasada desde el controlador.
$tituloPagina = $titulo;

// 2. Incluye la cabecera del panel.
include __DIR__ . '/../../layouts/admin-header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo htmlspecialchars($titulo); ?></h3>
        <div class="card-tools">
            <a href="/panel/usuarios/crear" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir Nuevo Usuario
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped projects">
            <thead>
                <tr>
                    <th style="width: 1%">#</th>
                    <th style="width: 30%">Nombre de Usuario</th>
                    <th>Correo Electrónico</th>
                    <th>Rol</th>
                    <th>Miembro desde</th>
                    <th style="width: 20%"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Conversión del bucle @forelse de Blade
                if ($usuarios->count() > 0):
                    foreach ($usuarios as $usuario):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario->id); ?></td>
                    <td>
                        <a><?php echo htmlspecialchars($usuario->nombremostrado ?: $usuario->nombreusuario); ?></a>
                        <br>
                        <small>Último acceso: <?php echo htmlspecialchars($usuario->updated_at->diffForHumans()); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($usuario->correoelectronico); ?></td>
                    <td>
                        <?php if ($usuario->rol === 'admin'): ?>
                        <span class="badge badge-success">Administrador</span>
                        <?php else: ?>
                        <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($usuario->rol)); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($usuario->created_at->format('d/m/Y')); ?></td>
                    <td class="project-actions text-right">
                        <a class="btn btn-info btn-sm" href="/panel/usuarios/editar/<?php echo htmlspecialchars($usuario->id); ?>">
                            <i class="fas fa-pencil-alt"></i> Editar
                        </a>
                        <button class="btn btn-danger btn-sm" data-id="<?php echo htmlspecialchars($usuario->id); ?>" data-url="/panel/usuarios/eliminar">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                    // Esto equivale a la sección @empty de Blade
                ?>
                <tr>
                    <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <?php
        // Conversión de la paginación.
        // El método links() generará el HTML de la paginación.
        if ($usuarios->hasPages()) {
            echo $usuarios->links();
        }
        ?>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
include __DIR__ . '/../../layouts/admin-footer.php';
?>
