<?php
// 1. Define el título de la página.
$tituloPagina = 'Crear Nueva Página';

// 2. Incluye la cabecera del panel.
include __DIR__ . '/../layouts/admin-header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
        <p>Rellena los campos para crear una nueva página</p>
        <a href="/panel/paginas" class="btn-volver">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/store" method="POST">
        <?php
        // Reemplazo de @csrf. Esta función imprime el input oculto con el token.
        echo csrf_field();
        ?>
        <div class="cuerpo-formulario">

            <?php
            // Bloque para mostrar mensajes de error de validación.
            // La variable $errors es inyectada por el framework al redirigir con errores.
            // Se añade isset() por seguridad.
            if (isset($errors) && $errors->any()):
            ?>
                <div class="alerta alerta-error">
                    <ul>
                        <?php foreach ($errors->all() as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php // Campo para el Título ?>
            <div class="grupo-formulario">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', '')); ?>" required>
            </div>
            
            <?php // Asumo que 'subtitulo' es una columna en la tabla 'paginas' ?>
            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars(old('subtitulo', '')); ?>">
            </div>

            <?php // CAMPO DE METADATOS DE EJEMPLO ?>
            <div class="grupo-formulario">
                <label for="meta_autor_invitado">Autor Invitado (Metadato)</label>
                <input type="text" id="meta_autor_invitado" name="meta[autor_invitado]" placeholder="Ej: Dr. Juan Pérez" value="<?php echo htmlspecialchars(old('meta.autor_invitado', '')); ?>">
                <small>Este campo se guarda en la tabla de metadatos.</small>
            </div>

            <?php // Campo para el Contenido ?>
            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí..."><?php echo htmlspecialchars(old('contenido', '')); ?></textarea>
            </div>

            <?php // Campo para el Estado ?>
            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="borrador" <?php echo old('estado', 'borrador') == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo old('estado') == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                </select>
            </div>

        </div>

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Crear Página</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
include __DIR__ . '/../layouts/admin-footer.php';
?>