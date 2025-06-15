<?php
// 1. Define el título de la página.
$tituloPagina = 'Ajustes';

// 2. Incluye la cabecera del panel.
// Las variables $mensajeExito, $paginas, y $paginaInicioActual son pasadas desde el controlador.
include __DIR__ . '/../layouts/admin-header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Ajustes Generales</h1>
        </div>
        <div class="card-body">
            
            <?php // Muestra un mensaje de éxito si existe. ?>
            <?php if (!empty($mensajeExito)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($mensajeExito); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/panel/ajustes/guardar">
                <?php
                // ¡Añadido de seguridad importante!
                // La plantilla original no tenía token CSRF. Lo agregamos aquí.
                echo csrf_field();
                ?>

                <div class="form-group mb-3">
                    <label for="pagina_inicio" class="form-label"><strong>Página de inicio</strong></label>
                    <p class="form-text text-muted">Elige qué página se mostrará como portada de tu sitio web. Si seleccionas la opción por defecto, se mostrará la bienvenida del sistema.</p>
                    <select name="pagina_inicio" id="pagina_inicio" class="form-select">
                        <option value="">— Página de bienvenida por defecto —</option>
                        <?php if (isset($paginas) && !$paginas->isEmpty()): ?>
                            <?php foreach ($paginas as $pagina): ?>
                                <option value="<?php echo htmlspecialchars($pagina->slug); ?>" 
                                    <?php if (isset($paginaInicioActual) && $pagina->slug === $paginaInicioActual) { echo 'selected'; } ?>>
                                    <?php echo htmlspecialchars($pagina->titulo); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
include __DIR__ . '/../layouts/admin-footer.php';
?>