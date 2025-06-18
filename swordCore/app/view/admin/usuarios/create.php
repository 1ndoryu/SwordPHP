<?php

// 1. Define el título de la página y obtiene un posible mensaje de error de la sesión.
$tituloPagina = 'Añadir Nuevo Usuario';
// La variable $errorMessage es pasada directamente desde el controlador.

// 2. Incluye la cabecera del panel de administración.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<form action="/panel/usuarios/crear" method="POST">
    <div class="bloque formulario-contenedor">

        <div class="cabecera-formulario">
            <p>Rellena los campos para crear un nuevo usuario</p>
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
                <input type="text" id="nombremostrado" name="nombremostrado" placeholder="Nombre público del usuario" value="<?php echo htmlspecialchars(old('nombremostrado', '')); ?>">
            </div>

            <div class="grupo-formulario">
                <label for="nombreusuario">Nombre de Usuario</label>
                <input type="text" id="nombreusuario" name="nombreusuario" placeholder="login_de_usuario" value="<?php echo htmlspecialchars(old('nombreusuario', '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="correoelectronico">Correo Electrónico</label>
                <input type="email" id="correoelectronico" name="correoelectronico" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars(old('correoelectronico', '')); ?>" required>
            </div>

            <hr>

            <h4>Contraseña</h4>
            <div class="grupo-formulario">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave" placeholder="Introduce la contraseña" required>
            </div>
            <div class="grupo-formulario">
                <label for="clave_confirmation">Confirmar Contraseña</label>
                <input type="password" id="clave_confirmation" name="clave_confirmation" placeholder="Confirma la contraseña" required>
            </div>

            <hr>

            <?php
            // Incluimos el gestor de metadatos.
            // Repoblamos con 'old' si la validación del formulario falla.
            $old_meta_array = old('meta', []);
            $metadatos_para_componente = collect($old_meta_array)->map(function ($item) {
                return (object) [
                    'meta_key' => $item['clave'] ?? null,
                    'meta_value' => $item['valor'] ?? null,
                ];
            });
            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $metadatos_para_componente]
            );
            ?>

        </div>
    </div>

    <div class="bloque segundoContenedor">
        <div class="grupo-formulario estado">
            <label for="rol">Rol del Usuario</label>
            <select id="rol" name="rol">
                <?php $rolSeleccionado = old('rol', 'suscriptor'); ?>
                <option value="suscriptor" <?php echo $rolSeleccionado == 'suscriptor' ? 'selected' : ''; ?>>Suscriptor</option>
                <option value="admin" <?php echo $rolSeleccionado == 'admin' ? 'selected' : ''; ?>>Administrador</option>
            </select>
        </div>

        <div class="pie-formulario">
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>

<?php
// 3. Incluye el pie de página del panel de administración.
echo partial('layouts/admin-footer', []);
?>