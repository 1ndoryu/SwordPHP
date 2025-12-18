<?php

/**
 * Componente de modal de confirmación reutilizable.
 * 
 * Variables requeridas:
 * @var string $modalId ID único del modal (requerido)
 * @var string $titulo Título del modal
 * @var string $mensaje Mensaje de confirmación
 * @var string $mensajeId ID del elemento del mensaje (para actualizarlo dinámicamente)
 * @var string $botonConfirmarId ID del botón de confirmar
 * @var string $textoConfirmar Texto del botón de confirmar
 * @var string $textoCancelar Texto del botón de cancelar
 * @var string $funcionCerrar Nombre de la función JS para cerrar el modal
 * @var bool $esPeligroso Si es true, el botón de confirmar es rojo
 */

$modalId = $modalId ?? 'modalConfirmacion';
$titulo = $titulo ?? 'Confirmar accion';
$mensaje = $mensaje ?? 'Esta seguro de continuar?';
$mensajeId = $mensajeId ?? 'mensajeModal';
$botonConfirmarId = $botonConfirmarId ?? 'botonConfirmar';
$textoConfirmar = $textoConfirmar ?? 'Confirmar';
$textoCancelar = $textoCancelar ?? 'Cancelar';
$funcionCerrar = $funcionCerrar ?? "cerrarModal_$modalId";
$esPeligroso = $esPeligroso ?? true;
?>

<div id="<?= htmlspecialchars($modalId) ?>" class="modalOverlay" style="display: none;">
    <div class="modalContenido">
        <h3><?= htmlspecialchars($titulo) ?></h3>
        <p id="<?= htmlspecialchars($mensajeId) ?>"><?= htmlspecialchars($mensaje) ?></p>
        <div class="modalAcciones">
            <button type="button" class="botonSecundario" onclick="<?= htmlspecialchars($funcionCerrar) ?>()">
                <?= htmlspecialchars($textoCancelar) ?>
            </button>
            <button type="button" class="<?= $esPeligroso ? 'botonPeligro' : 'botonPrimario' ?>" id="<?= htmlspecialchars($botonConfirmarId) ?>">
                <?= htmlspecialchars($textoConfirmar) ?>
            </button>
        </div>
    </div>
</div>

<script>
    /* 
     * Funciones autogeneradas para el modal <?= $modalId ?>
     */
    function <?= $funcionCerrar ?>() {
        document.getElementById('<?= $modalId ?>').style.display = 'none';
    }

    document.getElementById('<?= $modalId ?>')?.addEventListener('click', function(e) {
        if (e.target === this) {
            <?= $funcionCerrar ?>();
        }
    });
</script>