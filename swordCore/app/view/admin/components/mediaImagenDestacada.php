<?php

/**
 * Componente de UI para gestionar la imagen destacada.
 *
 * @var ?int $idImagenDestacada El ID de la imagen destacada actual.
 * @var ?string $urlImagenDestacada La URL de la imagen para la vista previa.
 * @var string $nombreInput El nombre para el campo de formulario (input).
 */

$idImagenDestacada = $idImagenDestacada ?? null;
$urlImagenDestacada = $urlImagenDestacada ?? '';
$nombreInput = $nombreInput ?? '_imagen_destacada_id';

// Determina si hay una imagen para mostrar
$tieneImagen = !empty($idImagenDestacada) && !empty($urlImagenDestacada);
?>

<div class="bloque" id="gestorImagenDestacada">
    <h4>Imagen Destacada</h4>
    <div class="cuerpo-caja">
        <div class="contenedor-preview-imagen" id="previewImagenDestacada">
            <?php if ($tieneImagen): ?>
                <img src="<?= htmlspecialchars($urlImagenDestacada) ?>" alt="Imagen destacada">
            <?php endif; ?>
        </div>

        <input type="hidden" name="<?= htmlspecialchars($nombreInput) ?>" id="imagenDestacadaId" value="<?= htmlspecialchars((string) $idImagenDestacada) ?>">

        <div class="acciones-imagen-destacada">
            <button type="button" class="btnN" id="seleccionarImagenBtn">
                <?= $tieneImagen ? 'Cambiar imagen' : 'Seleccionar imagen' ?>
            </button>
            <button type="button" class="btnN IconoRojo" id="quitarImagenBtn" style="<?= $tieneImagen ? '' : 'display: none;' ?>">
                Quitar imagen
            </button>
        </div>
    </div>
</div>

<div id="modalSeleccionMedios" class="modal-sword" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-cabecera">
            <h3>Biblioteca de Medios</h3>
            <span class="modal-cerrar">&times;</span>
        </div>
        <div class="modal-cuerpo">
            <div id="galeriaModalContenedor">
                <p>Cargando medios...</p>
            </div>
        </div>
        <div class="modal-pie">
            <button type="button" class="btnN" id="cancelarSeleccionBtn">Cancelar</button>
        </div>
    </div>
</div>

<style>
    /* Estilos para el Gestor y el Modal */
    #gestorImagenDestacada .cuerpo-caja {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .contenedor-preview-imagen {
        width: 100%;
        min-height: 50px;
        border: var(--borde);
        border-radius: var(--radius);
        background-color: var(--fondo-claro);
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    .contenedor-preview-imagen img {
        max-width: 100%;
        height: auto;
        display: block;
    }

    .acciones-imagen-destacada {
        display: flex;
        gap: 10px;
    }

    /* Estilos del Modal */
    .modal-sword {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-contenido {
        background-color: var(--fondo);
        margin: 5% auto;
        padding: 20px;
        border: var(--borde);
        width: 80%;
        max-width: 900px;
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
    }

    .modal-cabecera {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: var(--borde);
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .modal-cabecera h3 {
        margin: 0;
    }

    .modal-cerrar {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .modal-cuerpo {
        max-height: 60vh;
        overflow-y: auto;
    }

    #galeriaModalContenedor {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .modal-media-item {
        width: calc(20% - 12px);
        /* 5 por fila */
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }

    .modal-media-item:hover {
        border-color: #0073aa;
    }

    .modal-media-item.seleccionado {
        border-color: #0073aa;
        box-shadow: 0 0 0 3px #0073aa;
    }

    .modal-media-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }

    .modal-pie {
        border-top: var(--borde);
        padding-top: 15px;
        margin-top: 15px;
        display: flex;
        justify-content: flex-end;
    }
</style>