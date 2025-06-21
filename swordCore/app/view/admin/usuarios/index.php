<?php
// Usar $tituloPagina que se pasa desde el controlador.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Gestión de Usuarios']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="tituloVista">
            <h1><?php echo htmlspecialchars($tituloPagina ?? 'Gestión de Usuarios'); ?></h1>
        </div>
        <div class="accionesVista">
            <a href="/panel/usuarios/crear" class="btnCrear">
                Añadir Usuario
            </a>
        </div>
    </div>

    <?php // Formulario de Filtros ?>
    <div class="filtrosListado">
        <form action="/panel/usuarios" method="GET" id="filtrosFormUsuarios">
            <div class="campoFiltro">
                <label for="search_term">Buscar:</label>
                <input type="text" name="search_term" id="search_term" value="<?php echo htmlspecialchars($filtrosActuales['search_term'] ?? ''); ?>" placeholder="Nombre, email...">
            </div>
            <div class="campoFiltro">
                <label for="date_filter">Fecha Registro:</label>
                <input type="date" name="date_filter" id="date_filter" value="<?php echo htmlspecialchars($filtrosActuales['date_filter'] ?? ''); ?>">
            </div>
            <div class="campoFiltro">
                <label for="role_filter">Rol:</label>
                <select name="role_filter" id="role_filter">
                    <option value="">Todos los roles</option>
                    <?php foreach ($rolesDisponibles as $rolKey): ?>
                        <option value="<?php echo htmlspecialchars($rolKey); ?>" <?php echo (($filtrosActuales['role_filter'] ?? '') == $rolKey) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($rolKey)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="accionesFiltro">
                <button type="submit" class="btnFiltrar">Filtrar</button>
                <a href="/panel/usuarios" class="btnLimpiar">Limpiar</a>
            </div>
        </form>
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

        <div class="listaContenido" id="listaUsuariosContainer">
            <?php if ($usuarios->count() > 0): ?>
                <?php foreach ($usuarios as $usuario): ?>
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
                            <a href="/panel/usuarios/editar/<?php echo htmlspecialchars($usuario->id); ?>" class="iconoB btnEditar" title="Editar">
                                <?php echo icon('edit'); ?>
                            </a>

                            <form action="/panel/usuarios/eliminar/<?php echo htmlspecialchars($usuario->id); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');" style="display: inline-block;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="iconoB IconoRojo btnEliminar" title="Eliminar">
                                    <?php echo icon('borrar'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron usuarios con los filtros aplicados.
                </div>
            <?php endif; ?>
        </div>

        <div class="paginacion">
            <?php
            if ($usuarios->hasPages()) {
                 // Pasar también los query params actuales para que la paginación los mantenga
                echo renderizarPaginacion($usuarios->currentPage(), $usuarios->lastPage(), $usuarios->path(), $filtrosActuales);
            }
            ?>
        </div>
    </div>
</div>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>