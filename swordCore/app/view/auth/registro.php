<?php
// 1. Define el título que usará el header.php.
$titulo = $titulo ?? 'Registro';

// 2. Incluye la cabecera global del sitio.
// Asumimos que este archivo está en swordCore/app/view/auth/
include __DIR__ . '/../layouts/header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div style="width: 100%; max-width: 500px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2><?php echo htmlspecialchars($titulo); ?></h2>

    <?php // Bloque para mostrar mensajes de error o éxito que enviamos desde el controlador ?>
    <?php if (session()->has('error')): ?>
        <p style="color: #D8000C; background-color: #FFBABA; padding: 10px; border-radius: 4px;">
            <?php echo htmlspecialchars(session('error')); ?>
        </p>
    <?php endif; ?>
    <?php if (session()->has('exito')): ?>
        <p style="color: #4F8A10; background-color: #DFF2BF; padding: 10px; border-radius: 4px;">
            <?php echo htmlspecialchars(session('exito')); ?>
        </p>
    <?php endif; ?>

    <form action="/registro" method="POST">
        <?php
        // Reemplazo del token CSRF por la función helper estándar.
        echo csrf_field();
        ?>

        <div style="margin-bottom: 15px;">
            <label for="nombreusuario">Nombre de Usuario:</label><br>
            <input type="text" id="nombreusuario" name="nombreusuario" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="correoelectronico">Correo Electrónico:</label><br>
            <input type="email" id="correoelectronico" name="correoelectronico" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="clave">Contraseña:</label><br>
            <input type="password" id="clave" name="clave" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="nombremostrado">Nombre a Mostrar (Opcional):</label><br>
            <input type="text" id="nombremostrado" name="nombremostrado" style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" style="width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Crear Cuenta
        </button>
    </form>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página global para cerrar la página.
include __DIR__ . '/../layouts/footer.php';
?>