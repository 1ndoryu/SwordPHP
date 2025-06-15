<?php
// 1. Define el título que usará el header.php.
// La variable $titulo es pasada desde el controlador, con un valor por defecto.
$titulo = $titulo ?? 'Iniciar Sesión';

// 2. Incluye la cabecera global del sitio.
// Asumimos que este archivo está en swordCore/app/view/, por lo que ajustamos la ruta.
include __DIR__ . '/../layouts/header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div style="width: 100%; max-width: 500px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2><?php echo htmlspecialchars($titulo); ?></h2>

    <?php
    // Bloque para mostrar mensajes de éxito (ej: tras registro)
    if (isset($exito) && $exito):
    ?>
        <p style="color: #4F8A10; background-color: #DFF2BF; padding: 10px; border-radius: 4px;">
            <?php echo htmlspecialchars($exito); ?>
        </p>
    <?php endif; ?>

    <?php
    // Bloque para mostrar mensajes de error (ej: credenciales incorrectas)
    if (isset($error) && $error):
    ?>
        <p style="color: #D8000C; background-color: #FFBABA; padding: 10px; border-radius: 4px;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <form action="/login" method="POST">
        <?php
        // Campo oculto para el token CSRF, esencial para la seguridad.
        echo csrf_field();
        ?>

        <div style="margin-bottom: 15px;">
            <label for="identificador">Correo Electrónico o Usuario:</label><br>
            <input type="text" id="identificador" name="identificador" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="clave">Contraseña:</label><br>
            <input type="password" id="clave" name="clave" required style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" style="width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Iniciar Sesión
        </button>
    </form>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página global para cerrar la página.
include __DIR__ . '/../layouts/footer.php';
?>