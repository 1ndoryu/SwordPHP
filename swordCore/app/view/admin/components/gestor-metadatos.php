<?php

/**
 * Componente para gestionar metadatos (campos personalizados).
 *
 * Este componente genera una interfaz de usuario para añadir, ver, editar y eliminar
 * pares de clave-valor. Está diseñado para ser incluido en formularios de creación
 * o edición de contenido. No depende de ningún framework CSS.
 *
 * @var ?\Illuminate\Support\Collection $metadatos Colección de objetos de metadatos existentes.
 * Se espera que cada objeto en la colección tenga las propiedades 'clave' y 'valor'.
 * Si no se proporciona, se inicializa como una colección vacía.
 */

// Si la variable $metadatos no está definida al incluir el archivo, se crea una colección vacía para evitar errores.
$metadatos = $metadatos ?? collect([]);
?>

<div class="bloque gestorMetadatos" id="gestorMetadatosContenedor">
    <h4>Campos Personalizados</h4>

    <div id="metadatosExistentes">
        <?php foreach ($metadatos as $index => $meta): ?>
            <div class="metaPar">
                <div class="metaPar-clave">
                    <label for="meta_clave_<?= $index ?>">Nombre del campo</label>
                    <?php // CORRECCIÓN: Usar meta_key para mostrar el valor existente.
                    ?>
                    <input id="meta_clave_<?= $index ?>" type="text" name="meta[<?= $index ?>][clave]" value="<?= htmlspecialchars($meta->meta_key ?? '') ?>" placeholder="ej: autor_invitado">
                </div>
                <div class="metaPar-valor">
                    <label for="meta_valor_<?= $index ?>">Valor</label>
                    <?php // CORRECCIÓN: Usar meta_value para mostrar el valor existente.
                    ?>
                    <textarea id="meta_valor_<?= $index ?>" name="meta[<?= $index ?>][valor]" rows="1" placeholder="Valor del campo"><?= htmlspecialchars($meta->meta_value ?? '') ?></textarea>
                </div>
                <div class="metaPar-accion">
                    <button type="button" class="eliminarMetaPar" title="Eliminar campo">&times;</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="btnN" id="agregarMetaBtn">Agregar Campo</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contenedor = document.getElementById('gestorMetadatosContenedor');
        if (!contenedor) return;

        const agregarBtn = document.getElementById('agregarMetaBtn');
        const metadatosExistentesContenedor = document.getElementById('metadatosExistentes');
        let metaIndex = <?= $metadatos->count() ?>;

        if (!agregarBtn || !metadatosExistentesContenedor) return;

        agregarBtn.addEventListener('click', function() {
            const nuevoParHtml = `
            <div class="metaPar">
                <div class="metaPar-clave">
                    <label for="meta_clave_${metaIndex}">Nombre del campo</label>
                    <input id="meta_clave_${metaIndex}" type="text" name="meta[${metaIndex}][clave]" placeholder="ej: autor_invitado">
                </div>
                <div class="metaPar-valor">
                    <label for="meta_valor_${metaIndex}">Valor</label>
                    <textarea id="meta_valor_${metaIndex}" name="meta[${metaIndex}][valor]" rows="1" placeholder="Valor del campo"></textarea>
                </div>
                <div class="metaPar-accion">
                    <button type="button" class="eliminarMetaPar" title="Eliminar campo">&times;</button>
                </div>
            </div>
        `;
            metadatosExistentesContenedor.insertAdjacentHTML('beforeend', nuevoParHtml);
            metaIndex++;
        });

        contenedor.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('eliminarMetaPar')) {
                e.target.closest('.metaPar').remove();
            }
        });
    });
</script>

<style>
    .gestorMetadatos {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .gestorMetadatos h4 {
        margin-top: 0;
        margin-bottom: 10px;
        /* border-bottom: 1px solid #eee; */
        /* padding-bottom: 10px; */
    }

    .metaPar {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        margin-bottom: 15px;
    }

    .metaPar:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .metaPar-clave,
    .metaPar-valor {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .metaPar-clave {
        flex: 1;
    }

    .metaPar-valor {
        flex: 2;
    }

    .metaPar-accion {
        margin-left: auto;
    }

    .metaPar label {
        font-size: 0.9em;
        margin-bottom: 4px;
        font-weight: bold;
    }

    .metaPar input[type="text"],
    .metaPar textarea {
        width: 100%;
        border: var(--borde);
        border-radius: 3px;
        box-sizing: border-box;
        margin: 0px;
        padding: 6px 10px !important;
        height: 32px;
        color: unset !important;
    }

    .eliminarMetaPar {
        line-height: 1;
        padding: 8px 12px;
        height: 14px;
        font-size: 20px;
        border: var(--borde);
    }

    #agregarMetaBtn {
        margin-top: 10px;
    }
</style>