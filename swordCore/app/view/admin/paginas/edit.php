<?php
$tituloPagina = 'Editar Página';
echo partial('layouts/admin-header', []);
?>

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
        <p>Editando: <strong><?php echo htmlspecialchars($pagina->titulo ?? ''); ?></strong></p>
        <a href="/panel/paginas" class="btnN">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/update/<?php echo htmlspecialchars($pagina->id ?? ''); ?>" method="POST">
        <?php
        echo csrf_field();
        ?>
        <div class="cuerpo-formulario">

            <?php // REFACTOR: Mostrar mensaje de error pasado desde el controlador. ?>
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

            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <?php
                    $estadoActual = old('estado', $pagina->estado ?? 'borrador');
                    ?>
                    <option value="borrador" <?php echo $estadoActual == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo $estadoActual == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                </select>
            </div>

            <?php
            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $pagina->metas]
            );
            ?>

        </div>

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Guardar Cambios</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<?php
echo partial('layouts/admin-footer', []);
?>