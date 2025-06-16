<?php
// 1. Define las variables y el título usando la configuración genérica.
$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['add_new_item'] ?? 'Añadir Nuevo');
$errorMessage = session()->pull('error'); // Obtenemos el mensaje de error si existe

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', []);
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
        <p>Rellena los campos para crear una nueva entrada de "<?php echo htmlspecialchars($labels['singular_name'] ?? 'Contenido'); ?>"</p>
        <a href="/panel/<?php echo $slug; ?>" class="btnN">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/<?php echo $slug; ?>/crear" method="POST">
        <?php echo csrf_field(); ?>
        <div class="cuerpo-formulario">

            <?php if (!empty($errorMessage)): ?>
                <div class="alerta alerta-error" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="grupo-formulario">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido aquí..."><?php echo htmlspecialchars(old('contenido', '')); ?></textarea>
            </div>

            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="borrador" <?php echo old('estado', 'borrador') == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo old('estado') == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                </select>
            </div>

            <?php
            // Prepara los metadatos antiguos (si existen por un error de validación) para el componente.
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

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Crear <?php echo htmlspecialchars($labels['singular_name'] ?? 'Entrada'); ?></button>
            <a href="/panel/<?php echo $slug; ?>" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>