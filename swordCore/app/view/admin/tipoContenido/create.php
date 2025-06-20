<?php

$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['add_new_item'] ?? 'Añadir Nuevo');
$errorMessage = session()->pull('error');

echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<form action="/panel/<?php echo $slug; ?>/crear" method="POST">
    <div class="bloque formulario-contenedor">

        <div class="cabecera-formulario">
            <p>Rellena los campos para crear una nueva entrada de "<?php echo htmlspecialchars($labels['singular_name'] ?? 'Contenido'); ?>"</p>
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
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="<?php echo htmlspecialchars(old('titulo', '')); ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="5" placeholder="Escribe el contenido aquí..."><?php echo htmlspecialchars(old('contenido', '')); ?></textarea>
            </div>

            <?php
            $old_meta_array = old('meta', []);
            $metadatos_para_componente = collect($old_meta_array)->mapWithKeys(function ($item) {
                if (isset($item['clave']) && trim($item['clave']) !== '') {
                    return [trim($item['clave']) => $item['valor'] ?? ''];
                }
                return [];
            })->all();

            echo partial(
                'admin/components/gestor-metadatos',
                ['metadatos' => $metadatos_para_componente]
            );
            ?>

        </div>
    </div>

    <div class="bloque segundoContenedor">
        <?php
        $idDestacada = old('_imagen_destacada_id');
        $urlDestacada = '';
        if ($idDestacada) {
            $media = \App\model\Media::find($idDestacada);
            if ($media) {
                $urlDestacada = $media->url_publica;
            }
        }
        echo partial('admin/components/mediaImagenDestacada', [
            'idImagenDestacada' => $idDestacada,
            'urlImagenDestacada' => $urlDestacada,
        ]);
        ?>

        <div class="grupo-formulario estado">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="borrador" <?php echo old('estado', 'borrador') == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                <option value="publicado" <?php echo old('estado') == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
            </select>
        </div>

        <div class="pie-formulario">
            <button type="submit" class="btnN icono verde"><?php echo icon('checkCircle') ?></button>
        </div>
    </div>
</form>

<?php
echo partial('layouts/admin-footer', []);
?>