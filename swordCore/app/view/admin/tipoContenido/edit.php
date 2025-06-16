<?php

$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['edit_item'] ?? 'Editar Entrada');
$errorMessage = session()->pull('error');


echo partial('layouts/admin-header', []);
?>


<form action="/panel/<?php echo $slug; ?>/editar/<?php echo htmlspecialchars($entrada->id ?? ''); ?>" method="POST">
    <div class="formulario-contenedor">

        <div class="cabecera-formulario">
            <p>Editando "<?php echo htmlspecialchars($labels['singular_name'] ?? 'Entrada'); ?>": <strong><?php echo htmlspecialchars($entrada->titulo ?? ''); ?></strong></p>
            <a href="/panel/<?php echo $slug; ?>" class="btnN">
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
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', $entrada->titulo ?? '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="5" placeholder="Escribe el contenido aquí..."><?php echo htmlspecialchars(old('contenido', $entrada->contenido ?? '')); ?></textarea>
            </div>



            <?php
            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $entrada->metas]
            );
            ?>

        </div>
    </div>

    <div class="segundoContenedor">
        <div class="grupo-formulario estado">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php $estadoActual = old('estado', $entrada->estado ?? 'borrador'); ?>
                <option value="borrador" <?php echo $estadoActual == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                <option value="publicado" <?php echo $estadoActual == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
            </select>
        </div>

        <div class="pie-formulario">
            <button class="btnN icono"><?php echo icon('borrar') ?></button>
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>


<?php
echo partial('layouts/admin-footer', []);
?>