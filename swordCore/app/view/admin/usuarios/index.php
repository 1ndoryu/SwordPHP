<?php
// 1. Define el título de la página.
$tituloPagina = 'Gestión de Usuarios';

// 2. Incluye la cabecera del panel.
include __DIR__ . '/../../layouts/admin-header.php';
?>

<div class="vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
            <a href="/panel/usuarios/crear" class="btnCrear">
                Añadir
            </a>
        </div>
    </div>

    <?php // Bloque para mostrar mensajes de éxito o error ?>
    <?php if (session()->has('success')): ?>
        <div class="alerta alertaExito" role="alert">
            <?php echo htmlspecialchars(session('success')); ?>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alerta alertaError" role="alert">
            <?php echo htmlspecialchars(session('error')); ?>
        </div>
    <?php endif; ?>

    <div class="contenidoVista">

        <div class="listaContenido">
            <?php
            // Se comprueba si la colección de usuarios no está vacía.
            if ($usuarios->count() > 0):
                foreach ($usuarios as $usuario):
            ?>
                    <div class="contenidoCard">
                        <div class="contenidoInfo">
                            <div class="infoItem iconoB iconoG">
                                <?php echo icon('user'); ?>
                            </div>
                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($usuario->nombremostrado ?: $usuario->nombreusuario); ?></span>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($usuario->correoelectronico); ?></span>
                            </div>
                            <div class="infoItem">
                                <?php if ($usuario->rol === 'admin'): ?>
                                    <span class="badge badgePublicado">Administrador</span>
                                <?php else: ?>
                                    <span class="badge badgeBorrador"><?php echo htmlspecialchars(ucfirst($usuario->rol)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($usuario->created_at->format('d/m/Y')); ?></span>
                            </div>
                        </div>

                        <div class="contenidoAcciones">
                            <a href="/panel/usuarios/editar/<?php echo htmlspecialchars($usuario->id); ?>" class="iconoB btnEditar">
                                <?php echo icon('edit'); ?>
                            </a>

                            <form action="/panel/usuarios/eliminar/<?php echo htmlspecialchars($usuario->id); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="iconoB IconoRojo btnEliminar">
                                    <?php echo icon('borrar'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php
                endforeach;
            else: // Si no hay usuarios que mostrar
                ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron usuarios.
                </div>
            <?php endif; ?>
        </div>

        <div class="paginacion">
            <?php
            // Adaptamos la paginación al nuevo helper.
            // Asumimos que el objeto $usuarios es un paginador de Laravel/Symfony.
            if ($usuarios->hasPages()) {
                // Pasamos la página actual y el total de páginas al helper.
                echo renderizarPaginacion($usuarios->currentPage(), $usuarios->lastPage());
            }
            ?>
        </div>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
include __DIR__ . '/../../layouts/admin-footer.php';
?>