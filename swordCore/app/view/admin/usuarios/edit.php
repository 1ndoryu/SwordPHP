<?php

// 1. Define el título de la página y obtiene un posible mensaje de error de la sesión.
$tituloPagina = 'Editar Usuario';
// La variable $errorMessage es pasada directamente desde el controlador.

// 2. Incluye la cabecera del panel de administración.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<form action="/panel/usuarios/update/<?php echo htmlspecialchars($usuario->id ?? ''); ?>" method="POST">
    <div class="bloque formulario-contenedor">

        <div class="cabecera-formulario">
            <p>Editando Usuario: <strong><?php echo htmlspecialchars($usuario->nombremostrado ?: $usuario->nombreusuario); ?></strong></p>
            <a href="/panel/usuarios" class="btnN">
                &larr; Volver al listado
            </a>
        </div>

        <?php echo csrf_field(); ?>
        <div class="cuerpo-formulario">

            <?php if (!empty($errorMessage)): ?>
                <div class="alerta alerta-error" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="grupo-formulario">
                <label for="nombremostrado">Nombre a Mostrar</label>
                <input type="text" id="nombremostrado" name="nombremostrado" placeholder="Nombre público del usuario" value="<?php echo htmlspecialchars(old('nombremostrado', $usuario->nombremostrado ?? '')); ?>">
            </div>

            <div class="grupo-formulario">
                <label for="correoelectronico">Correo Electrónico</label>
                <input type="email" id="correoelectronico" name="correoelectronico" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars(old('correoelectronico', $usuario->correoelectronico ?? '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="nombreusuario">Nombre de Usuario (no se puede cambiar)</label>
                <input type="text" id="nombreusuario" name="nombreusuario" value="<?php echo htmlspecialchars($usuario->nombreusuario ?? ''); ?>" readonly disabled>
                <small>El nombre de usuario no se puede modificar por seguridad.</small>
            </div>

            <hr>

            <h4>Cambiar Contraseña</h4>
            <p style="opacity: 0.7; font-size: 0.9em;">Deja estos campos en blanco para no modificar la contraseña actual.</p>
            <div class="grupo-formulario">
                <label for="clave">Nueva Contraseña</label>
                <input type="password" id="clave" name="clave" placeholder="Introduce la nueva contraseña">
            </div>
            <div class="grupo-formulario">
                <label for="clave_confirmation">Confirmar Nueva Contraseña</label>
                <input type="password" id="clave_confirmation" name="clave_confirmation" placeholder="Confirma la nueva contraseña">
            </div>

            <hr>

            <?php
            // Incluimos el gestor de metadatos, filtrando las claves internas
            $metadatosParaVista = array_filter(
                $usuario->metadata ?? [],
                fn($key) => !str_starts_with($key, '_'),
                ARRAY_FILTER_USE_KEY
            );
            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $metadatosParaVista]
            );
            ?>

        </div>
    </div>

    <div class="bloque segundoContenedor">
        <?php
        $idDestacada = $usuario->obtenerMeta('_imagen_destacada_id');
        $urlDestacada = '';
        if ($idDestacada) {
            $media = \App\model\Media::find($idDestacada);
            if ($media) {
                $urlDestacada = $media->url_publica;
            }
        }
        echo partial('admin/components/mediaImagenDestacada', [
            'idImagenDestacada' => $idDestacada,
            'urlImagenDestacada' => $urlDestacada,
        ]);
        ?>

        <div class="grupo-formulario estado">
            <label for="rol">Rol del Usuario</label>
            <select id="rol" name="rol">
                <?php $rolActual = old('rol', $usuario->rol ?? 'suscriptor'); ?>
                <option value="suscriptor" <?php echo $rolActual == 'suscriptor' ? 'selected' : ''; ?>>Suscriptor</option>
                <option value="admin" <?php echo $rolActual == 'admin' ? 'selected' : ''; ?>>Administrador</option>
            </select>
        </div>

        <div class="pie-formulario">
            <button type="button" class="btnN icono IconoRojo" onclick="eliminarRecurso('/panel/usuarios/eliminar/<?php echo htmlspecialchars($usuario->id); ?>', '<?php echo csrf_token(); ?>', '¿Estás seguro de que deseas eliminar este usuario? Si este usuario tiene contenido asociado, podría quedar huérfano.')">
                <?php echo icon('borrar'); ?>
            </button>
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>

<?php
// 3. Incluye el pie de página del panel de administración.
echo partial('layouts/admin-footer', []);
?>