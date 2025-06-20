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

<div class="gestordeMedios" id="gestorImagenDestacada">
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

<div id="modalSeleccionMedios" class="blque modal modal-sword" style="display: none;">
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
    .modal-cabecera {
        display: flex;
        flex-direction: row !important;
        justify-content: space-between;
        width: 100%;
    }

    div#modalSeleccionMedios {
        width: 600px;
    }

    div#galeriaModalContenedor img {
        max-width: 180px;
        max-height: 180px;
        object-fit: cover;
    }

    div#galeriaModalContenedor {
        display: flex;
        flex-direction: row;
        justify-content: space-evenly;
        width: 100%;
        gap: 10px;
        flex-wrap: wrap;
    }

    span.modal-cerrar {
        font-size: 20px;
        height: 20px;
        display: flex;
        align-content: center;
    }

    div#previewImagenDestacada img {
        max-width: 200px;
        max-height: 200px;
        object-fit: cover;
        width: 100%;
        height: 100%;
    }

    div#gestorImagenDestacada {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }

    .acciones-imagen-destacada button {
        width: -webkit-fill-available;
    }

    .acciones-imagen-destacada {
        display: flex;
        gap: 10px;
        width: 100%;
    }

    .cuerpo-caja {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .modal-cuerpo {
        width: 100%;
    }

    .modal-contenido {
        display: flex;
        gap: 20px;
    }
</style>