<?php
/**
 * Componente para gestionar metadatos (campos personalizados).
 * Adaptado para funcionar con un array asociativo de metadatos (JSONB).
 *
 * @var ?array $metadatos Array asociativo de clave-valor.
 */

// Si la variable $metadatos no está definida, se inicializa como un array vacío.
$metadatos = $metadatos ?? [];
$index = 0;
?>

<div class="bloque gestorMetadatos" id="gestorMetadatosContenedor">
    <h4>Campos Personalizados</h4>

    <div id="metadatosExistentes">
        <?php foreach ($metadatos as $clave => $valor): ?>
            <div class="metaPar">
                <div class="metaPar-clave">
                    <label for="meta_clave_<?= $index ?>">Nombre del campo</label>
                    <input id="meta_clave_<?= $index ?>" type="text" name="meta[<?= $index ?>][clave]" value="<?= htmlspecialchars($clave) ?>" placeholder="ej: autor_invitado">
                </div>
                <div class="metaPar-valor">
                    <label for="meta_valor_<?= $index ?>">Valor</label>
                    <?php
                    // Si el valor es un array o un objeto, lo mostramos como un string JSON.
                    $valorParaTextarea = is_array($valor) || is_object($valor) ? json_encode($valor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $valor;
                    ?>
                    <textarea id="meta_valor_<?= $index ?>" name="meta[<?= $index ?>][valor]" rows="1" placeholder="Valor del campo"><?= htmlspecialchars($valorParaTextarea) ?></textarea>
                </div>
                <div class="metaPar-accion">
                    <button type="button" class="eliminarMetaPar" title="Eliminar campo">&times;</button>
                </div>
            </div>
            <?php $index++; ?>
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
        // Inicializamos el índice del JS a partir del último índice renderizado por PHP.
        let metaIndex = <?= $index ?>;

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