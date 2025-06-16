<?php
// 1. Define el título de la página.
$tituloPagina = 'Crear Nueva Página';

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', []);
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
        <p>Rellena los campos para crear una nueva página</p>
        <a href="/panel/paginas" class="btn-volver">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/store" method="POST">
        <?php
        echo csrf_field();
        ?>
        <div class="cuerpo-formulario">

            <?php // REFACTOR: Mostrar mensaje de error pasado desde el controlador. 
            ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alerta alerta-error" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php // Campo para el Título 
            ?>
            <div class="grupo-formulario">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', '')); ?>" required>
            </div>

            <?php // Campo para el Subtítulo 
            ?>
            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars(old('subtitulo', '')); ?>">
            </div>

            <?php // Campo para el Contenido 
            ?>
            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí..."><?php echo htmlspecialchars(old('contenido', '')); ?></textarea>
            </div>

            <?php // Campo para el Estado 
            ?>
            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="borrador" <?php echo old('estado', 'borrador') == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo old('estado') == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                </select>
            </div>

            <?php
            // Se transforma el 'old input' (que es un array) a una colección de objetos
            // para que sea compatible con lo que espera el gestor de metadatos.
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
            <button type="submit" class="btn-principal">Crear Página</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>