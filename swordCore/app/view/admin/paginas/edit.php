<?php
// 1. Define el título de la página.
$tituloPagina = 'Editar Página';

// 2. Incluye la cabecera del panel.
// Las variables $pagina y $titulo son pasadas desde el controlador.
include __DIR__ . '/../../layouts/admin-header.php';
?>

<?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
        <p>Editando: <strong><?php echo htmlspecialchars($pagina->titulo ?? ''); ?></strong></p>
        <a href="/panel/paginas" class="btn-volver">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/update/<?php echo htmlspecialchars($pagina->id ?? ''); ?>" method="POST">
        <?php
        // Reemplazo de @csrf
        echo csrf_field();
        ?>
        <div class="cuerpo-formulario">

            <?php
            // Bloque para mostrar mensajes de error de sesión.
            if (session()->has('error')):
            ?>
                <div class="alerta alerta-error" role="alert">
                    <?php echo htmlspecialchars(session('error') ?? ''); ?>
                </div>
            <?php endif; ?>

            <?php // Campo para el Título ?>
            <div class="grupo-formulario">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', $pagina->titulo ?? '')); ?>" required>
            </div>

            <?php // Campo para el Subtítulo ?>
            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars(old('subtitulo', $pagina->subtitulo ?? '')); ?>">
            </div>

            <?php // CAMPO DE METADATOS DE EJEMPLO ?>
            <div class="grupo-formulario">
                <label for="meta_autor_invitado">Autor Invitado (Metadato)</label>
                <input type="text" id="meta_autor_invitado" name="meta[autor_invitado]" placeholder="Ej: Dr. Juan Pérez" value="<?php echo htmlspecialchars(old('meta.autor_invitado', $pagina->obtenerMeta('autor_invitado') ?? '')); ?>">
                <small>Este campo se guarda en la tabla de metadatos.</small>
            </div>

            <?php // Campo para el Contenido ?>
            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí..."><?php echo htmlspecialchars(old('contenido', $pagina->contenido ?? '')); ?></textarea>
            </div>

            <?php // Campo para el Estado ?>
            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <?php
                    // Lógica para determinar el estado seleccionado. Se añade 'borrador' como valor por defecto.
                    $estadoActual = old('estado', $pagina->estado ?? 'borrador');
                    ?>
                    <option value="borrador" <?php echo $estadoActual == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo $estadoActual == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                </select>
            </div>

        </div>

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Guardar Cambios</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>


<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
include __DIR__ . '/../../layouts/admin-footer.php';
?>