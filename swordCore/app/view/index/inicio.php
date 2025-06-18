<?php
// 1. Define el título que usará el header.php.
$titulo = 'Página de Inicio';

// 2. Incluye la cabecera global del sitio.
// Asumimos que este archivo está en swordCore/app/view/index/
include __DIR__ . '/../layouts/header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div style="padding-top: 50px; text-align: center; max-width: 600px; margin: auto;">
    <h1>SwordPHP</h1>
    <p>Un framework PHP minimalista, rápido y flexible.</p>
    <hr>
    <div>
        <p>
            <strong>Estado de la Base de Datos:</strong>
            <?php
            // La variable $estadoConexion es pasada desde el controlador.
            echo htmlspecialchars($estadoConexion);
            ?>
        </p>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página global para cerrar la página.
include __DIR__ . '/../layouts/footer.php';
?>