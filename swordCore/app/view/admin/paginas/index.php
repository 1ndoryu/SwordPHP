<?php
// 1. Define el título de la página.
$tituloPagina = 'Gestión de Páginas';

// 2. Incluye la cabecera del panel.
// La variable $paginas es pasada desde el controlador.
include __DIR__ . '/../layouts/admin-header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<div class="vista-listado">

    <div class="cabecera-vista">
        <div class="acciones-vista">
            <a href="/panel/paginas/create" class="btn-crear">
                Crear Nueva Página
            </a>
        </div>
    </div>

    <?php // Bloque para mostrar mensajes de éxito o error 
    ?>
    <?php if (session()->has('success')): ?>
        <div class="alerta alerta-exito" role="alert">
            <?php echo htmlspecialchars(session('success')); ?>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alerta alerta-error" role="alert">
            <?php echo htmlspecialchars(session('error')); ?>
        </div>
    <?php endif; ?>

    <div class="contenido-vista">
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th style="width: 10px">ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Estado</th>
                    <th>Fecha de Creación</th>
                    <th style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Conversión del bucle @forelse de Blade.
                if ($paginas->count() > 0):
                    foreach ($paginas as $pagina):
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pagina->id); ?></td>
                            <td><?php echo htmlspecialchars($pagina->titulo); ?></td>
                            <td><?php echo htmlspecialchars($pagina->autor->nombre ?? 'N/A'); ?></td>
                            <td>
                                <?php // Clases genéricas para los badges de estado 
                                ?>
                                <?php if ($pagina->estado == 'publicado'): ?>
                                    <span class="badge badge-publicado">Publicado</span>
                                <?php else: ?>
                                    <span class="badge badge-borrador">Borrador</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($pagina->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <a href="/panel/paginas/edit/<?php echo htmlspecialchars($pagina->id); ?>" class="btn-editar">
                                    Editar
                                </a>

                                <form action="/panel/paginas/destroy/<?php echo htmlspecialchars($pagina->id); ?>" method="POST" style="display:inline-block;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-eliminar">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                else: // Equivalente a @empty
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No se encontraron páginas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="paginacion">
            <?php
            // La paginación sigue funcionando igual.
            echo $paginas->links();
            ?>
        </div>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
include __DIR__ . '/../layouts/admin-footer.php';
?>