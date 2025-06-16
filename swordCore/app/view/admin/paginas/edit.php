<?php

// Se define el título de la página y se obtiene un posible mensaje de error de la sesión.
$tituloPagina = 'Editar Página';
$errorMessage = session()->pull('error');

// Se incluye la cabecera del panel de administración.
echo partial('layouts/admin-header', []);
?>

<form action="/panel/paginas/update/<?php echo htmlspecialchars($pagina->id ?? ''); ?>" method="POST">
    <div class="formulario-contenedor">

        <div class="cabecera-formulario">
            <p>Editando "Página": <strong><?php echo htmlspecialchars($pagina->titulo ?? ''); ?></strong></p>
            <a href="/panel/paginas" class="btnN">
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
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', $pagina->titulo ?? '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars(old('subtitulo', $pagina->subtitulo ?? '')); ?>">
            </div>

            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí..."><?php echo htmlspecialchars(old('contenido', $pagina->contenido ?? '')); ?></textarea>
            </div>

            <?php
            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $pagina->metas]
            );
            ?>

        </div>
    </div>

    <div class="segundoContenedor">
        <div class="grupo-formulario estado">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php $estadoActual = old('estado', $pagina->estado ?? 'borrador'); ?>
                <option value="borrador" <?php echo $estadoActual == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                <option value="publicado" <?php echo $estadoActual == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
            </select>
        </div>

        <div class="pie-formulario">
            <button type="button" class="btnN icono IconoRojo" onclick="eliminarRecurso('/panel/paginas/destroy/<?php echo htmlspecialchars($pagina->id); ?>', '<?php echo csrf_token(); ?>', '¿Estás seguro de que deseas eliminar esta página? Esta acción no se puede deshacer.')">
                <?php echo icon('borrar'); ?>
            </button>
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>

<?php
// Se incluye el pie de página del panel de administración.
echo partial('layouts/admin-footer', []);
?>