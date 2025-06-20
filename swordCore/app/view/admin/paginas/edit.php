<?php

// Se define el título de la página y se obtiene un posible mensaje de error de la sesión.
$tituloPagina = 'Editar Página';
$errorMessage = session()->pull('error');

// Se incluye la cabecera del panel de administración.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<form action="/panel/paginas/update/<?php echo htmlspecialchars($pagina->id ?? ''); ?>" method="POST">
    <div class="bloque formulario-contenedor">

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
                <label for="slug">Slug (URL)</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars(old('slug', $pagina->slug ?? '')); ?>">
            </div>

            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars(old('subtitulo', $pagina->subtitulo ?? '')); ?>">
            </div>

            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="5" placeholder="Escribe el contenido de la página aquí..."><?php echo htmlspecialchars(old('contenido', $pagina->contenido ?? '')); ?></textarea>
            </div>

            <?php
            // Filtramos el metadato interno de la plantilla antes de pasarlo a la vista.
            $metadatosParaVista = array_filter(
                $pagina->metadata ?? [],
                fn($key) => $key !== '_plantilla_pagina',
                ARRAY_FILTER_USE_KEY
            );
            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $metadatosParaVista]
            );
            ?>

        </div>
    </div>

    <div class="bloque segundoContenedor">
        <?php
        $idDestacada = $pagina->obtenerMeta('_imagen_destacada_id');
        $urlDestacada = '';
        if ($idDestacada) {
            $media = \App\model\Media::find($idDestacada);
            if ($media) {
                $urlDestacada = $media->url_publica;
            }
        }
        echo partial('admin/components/gestor-imagen-destacada', [
            'idImagenDestacada' => $idDestacada,
            'urlImagenDestacada' => $urlDestacada,
        ]);
        ?>
        <div class="grupo-formulario estado">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php $estadoActual = old('estado', $pagina->estado ?? 'borrador'); ?>
                <option value="borrador" <?php echo $estadoActual == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                <option value="publicado" <?php echo $estadoActual == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
            </select>
        </div>

        <?php if (!empty($plantillasDisponibles)): ?>
            <div class="grupo-formulario">
                <label for="plantilla_pagina">Plantilla</label>
                <select id="plantilla_pagina" name="_plantilla_pagina">
                    <option value="">Plantilla por defecto</option>
                    <?php
                    // Obtener la plantilla guardada para esta página, o la del old input si falló la validación
                    $plantillaGuardada = old('_plantilla_pagina', $pagina->obtenerMeta('_plantilla_pagina'));
                    foreach ($plantillasDisponibles as $archivo => $nombre):
                        $selected = ($archivo === $plantillaGuardada) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($archivo); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="pie-formulario">
            <button type="button" class="btnN icono IconoRojo" onclick="eliminarRecurso('/panel/paginas/destroy/<?php echo htmlspecialchars($pagina->id); ?>', '<?php echo csrf_token(); ?>', '¿Estás seguro de que deseas eliminar esta página? Esta acción no se puede deshacer.')">
                <?php echo icon('borrar'); ?>
            </button>
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>

<?php
echo partial('layouts/admin-footer', []);
?>