<?php

/**
 * Vista para la página de Ajustes de Enlaces Permanentes.
 *
 * Variables disponibles desde el controlador:
 * @var string $tituloPagina
 * @var string|null $mensajeExito
 * @var string $currentStructure La estructura de permalink guardada actualmente.
 */

echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="/panel/ajustes/enlaces-permanentes"]');
        if (!form) return;

        const customRadio = form.querySelector('input#custom-radio');
        const customField = form.querySelector('input#custom_structure_field');
        const radios = form.querySelectorAll('input[name="permalink_structure"]');

        function inicializarEstado() {
            const estructuraActual = "<?php echo addslashes($estructuraActual ?? '/%slug%/'); ?>";
            const esPersonalizada = <?php echo json_encode($esPersonalizada ?? false); ?>;

            customField.value = "<?php echo addslashes($valorInputPersonalizado ?? '/%slug%/'); ?>";

            if (esPersonalizada) {
                customRadio.checked = true;
            } else {
                for (const radio of radios) {
                    if (radio.value === estructuraActual) {
                        radio.checked = true;
                        break;
                    }
                }
            }
        }

        // Cuando se selecciona una opción predefinida, se actualiza el campo de texto.
        radios.forEach(radio => {
            if (radio.id !== 'custom-radio') {
                radio.addEventListener('change', () => {
                    if (radio.checked) {
                        customField.value = radio.value;
                    }
                });
            }
        });

        // Cuando se escribe o hace foco en el campo de texto, se selecciona la opción "personalizada".
        customField.addEventListener('input', () => {
            customRadio.checked = true;
        });
        customField.addEventListener('focus', () => {
            customRadio.checked = true;
        });

        // Establecer el estado inicial del formulario al cargar la página.
        inicializarEstado();
    });
</script>

<form method="POST" action="/panel/ajustes/enlaces-permanentes">
    <?php echo csrf_field(); ?>

    <div class="ajustesSword enlacesPermanentes">
        <?php if (!empty($mensajeExito)): ?>
            <div class="alerta alertaExito" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($mensajeExito); ?>
            </div>
        <?php endif; ?>

        <div class="bloque card">
            <div class="card-header">
                <h3 class="card-title">Ajustes de Enlaces Permanentes</h3>
            </div>
            <div class="card-body">
                <p>SwordPHP ofrece la posibilidad de crear una estructura de URL personalizada para tus enlaces permanentes y archivos. Las estructuras de URL personalizadas pueden mejorar la estética, usabilidad y compatibilidad futura de tus enlaces.</p>

                <fieldset class="grupo-formulario-radio">
                    <legend class="screen-reader-text">Ajustes Comunes</legend>

                    <div class="radio-item">
                        <input type="radio" name="permalink_structure" id="plain" value="/%slug%/" checked>
                        <label for="plain">
                            <span>Nombre de la entrada</span>
                            <code class="permalink-ejemplo"><?php echo config('app.url') ?>/pagina-ejemplo/</code>
                        </label>
                    </div>

                    <div class="radio-item">
                        <input type="radio" name="permalink_structure" id="day-name" value="/%año%/%mes%/%dia%/%slug%/">
                        <label for="day-name">
                            <span>Día y nombre</span>
                            <code class="permalink-ejemplo"><?php echo config('app.url') ?>/<?php echo date('Y/m/d'); ?>/pagina-ejemplo/</code>
                        </label>
                    </div>

                    <div class="radio-item">
                        <input type="radio" name="permalink_structure" id="month-name" value="/%año%/%mes%/%slug%/">
                        <label for="month-name">
                            <span>Mes y nombre</span>
                            <code class="permalink-ejemplo"><?php echo config('app.url') ?>/<?php echo date('Y/m'); ?>/pagina-ejemplo/</code>
                        </label>
                    </div>

                    <div class="radio-item">
                        <input type="radio" name="permalink_structure" id="numeric" value="/archivos/%id%/">
                        <label for="numeric">
                            <span>Numérico</span>
                            <code class="permalink-ejemplo"><?php echo config('app.url') ?>/archivos/123/</code>
                        </label>
                    </div>

                    <div class="radio-item">
                        <input type="radio" name="permalink_structure" id="custom-radio" value="custom">
                        <label for="custom-radio">
                            <span>Estructura personalizada</span>
                        </label>
                        <div class="custom-input-wrapper">
                            <code><?php echo config('app.url') ?></code>
                            <input type="text" name="custom_structure" id="custom_structure_field" class="custom_structure" value="/%año%/%slug%/">
                        </div>
                    </div>
                </fieldset>
                <button type="submit" class="btnN btn-primario">Guardar Cambios</button>
                <div class="oculto">
                    <h4>Opcional (Funcionalidad Futura)</h4>
                    <p>Si quieres, puedes usar una estructura personalizada para las URL de tus categorías y etiquetas. Por ejemplo, usando <code>temas</code> como tu base para categorías harás que los enlaces a las categorías se parezcan a <code>/temas/mi-categoria/</code>. Si dejas esto en blanco se usarán los valores por defecto.</p>

                    <div class="grupo-formulario">
                        <label for="category_base">Base de las categorías</label>
                        <input name="category_base" id="category_base" type="text" value="" class="regular-text code" disabled>
                    </div>

                    <div class="grupo-formulario">
                        <label for="tag_base">Base de las etiquetas</label>
                        <input name="tag_base" id="tag_base" type="text" value="" class="regular-text code" disabled>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

<style>
    .grupo-formulario-radio {
        border: 0;
        padding: 20px;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .screen-reader-text {
        border: 0;
        clip: rect(1px, 1px, 1px, 1px);
        clip-path: inset(50%);
        height: 1px;
        margin: -1px;
        overflow: hidden;
        padding: 0;
        position: absolute;
        width: 1px;
        word-wrap: normal !important;
    }

    .radio-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .radio-item input[type="radio"] {
        margin-top: 5px;
    }

    .radio-item label {
        display: flex;
        flex-direction: row;
        cursor: pointer;
        align-items: center;
        gap: 10px;
    }

    .radio-item label>span {
        font-weight: 600;
    }

    .permalink-ejemplo,
    .custom-input-wrapper>code {
        color: var(--texto-secundario);
        font-family: monospace;
        font-size: 0.9em;
        background-color: var(--fondo-claro);
        padding: 2px 5px;
        border-radius: 3px;
    }

    .custom_structure {
        flex-grow: 1;
    }

    .enlacesPermanentes .bloque.card {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .radio-item input {
        width: auto;
    }

    .custom-input-wrapper {
        display: flex;
        align-items: center;
        gap: 5px;
        height: 0px;
    }

    input#custom_structure_field {
        margin: 0px;
        padding: 0px !important;
    }
</style>

<?php echo partial('layouts/admin-footer', []); ?>