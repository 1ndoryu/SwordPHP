<?php

$tituloPagina = 'Crear Nueva Página';
$errorMessage = session()->pull('error');

echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<form action="/panel/paginas/store" method="POST">
    <div class="bloque formulario-contenedor">

        <div class="cabecera-formulario">
            <p>Rellena los campos para crear una nueva página</p>
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
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="<?php echo htmlspecialchars(old('subtitulo', '')); ?>">
            </div>

            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="5" placeholder="Escribe el contenido de la página aquí..."><?php echo htmlspecialchars(old('contenido', '')); ?></textarea>
            </div>

            <?php
            // Repoblamos el gestor de metadatos con datos antiguos si la validación falló.
            $old_meta_form_data = old('meta', []);
            $metadatos_para_componente = [];
            if (is_array($old_meta_form_data)) {
                foreach ($old_meta_form_data as $meta_item) {
                    // Se reconstruye el array asociativo [clave => valor] que espera el componente.
                    if (!empty($meta_item['clave'])) {
                        $metadatos_para_componente[trim($meta_item['clave'])] = $meta_item['valor'] ?? '';
                    }
                }
            }

            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $metadatos_para_componente]
            );
            ?>

        </div>
    </div>

    <div class="bloque segundoContenedor">
        <div class="grupo-formulario estado">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="borrador" <?php echo old('estado', 'borrador') == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                <option value="publicado" <?php echo old('estado') == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
            </select>
        </div>

        <?php if (!empty($plantillasDisponibles)): ?>
            <div class="grupo-formulario">
                <label for="plantilla_pagina">Plantilla</label>
                <select id="plantilla_pagina" name="_plantilla_pagina">
                    <option value="">Plantilla por defecto</option>
                    <?php
                    // Obtener la plantilla del old input si falló la validación
                    $plantillaSeleccionada = old('_plantilla_pagina');
                    foreach ($plantillasDisponibles as $archivo => $nombre):
                        $selected = ($archivo === $plantillaSeleccionada) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($archivo); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="pie-formulario">
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>

<?php
echo partial('layouts/admin-footer', []);
?>