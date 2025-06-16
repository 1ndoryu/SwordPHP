<?php
/**
 * Componente general para formularios de creación y edición.
 *
 * @param string $actionUrl La URL a la que se enviará el formulario.
 * @param string $method El método HTTP para el envío del formulario (ej. POST, PUT).
 * @param string $tituloFormulario El título o descripción que aparecerá en la cabecera del formulario.
 * @param string $textoBotonVolver Texto para el botón de volver al listado.
 * @param string $urlVolver URL para el botón de volver.
 * @param string $textoBotonSubmit Texto para el botón de envío principal.
 * @param string $iconoBotonSubmit (Opcional) Icono para el botón de envío principal.
 * @param string $id (Opcional) El ID de la entidad que se está editando, para construir la URL de acción.
 * @param string $contenidoPrincipalHTML HTML para los campos del cuerpo principal del formulario.
 * @param string $contenidoSecundarioHTML HTML para los campos del segundo contenedor (sidebar/estado).
 * @param string $botonEliminarHTML (Opcional) HTML para el botón de eliminar (usado en vistas de edición).
 * @param string $mensajeError (Opcional) Mensaje de error a mostrar.
 * @param string $_method_actual (Opcional) El método HTTP real (ej. PUT, PATCH, DELETE) si el formulario es POST.
 */

// Asegurarse de que las variables opcionales tengan un valor por defecto si no se proporcionan.
$iconoBotonSubmit = $iconoBotonSubmit ?? 'checkCircle';
$botonEliminarHTML = $botonEliminarHTML ?? '';
$mensajeError = $mensajeError ?? session()->pull('error'); // Intenta obtener de la sesión si no se pasa explícitamente.

$action = isset($id) && !empty($id) ? sprintf($actionUrl, $id) : $actionUrl;
$formMethod = strtoupper($method);
$_method_actual = $_method_actual ?? '';

?>

<form action="<?php echo htmlspecialchars($action); ?>" method="<?php echo $formMethod === 'POST' ? 'POST' : $formMethod; ?>">
    <?php if ($formMethod === 'POST' && !empty($_method_actual)): ?>
        <input type="hidden" name="_method" value="<?php echo htmlspecialchars(strtoupper($_method_actual)); ?>">
    <?php elseif ($formMethod !== 'POST' && $formMethod !== 'GET'): ?>
        <input type="hidden" name="_method" value="<?php echo htmlspecialchars($formMethod); ?>">
    <?php endif; ?>

    <div class="formulario-contenedor">
        <div class="cabecera-formulario">
            <p><?php echo htmlspecialchars($tituloFormulario); ?></p>
            <a href="<?php echo htmlspecialchars($urlVolver); ?>" class="btnN">
                &larr; <?php echo htmlspecialchars($textoBotonVolver); ?>
            </a>
        </div>

        <?php echo csrf_field(); ?>
        <div class="cuerpo-formulario">
            <?php if (!empty($mensajeError)): ?>
                <div class="alerta alerta-error" role="alert">
                    <?php echo htmlspecialchars($mensajeError); ?>
                </div>
            <?php endif; ?>

            <?php echo $contenidoPrincipalHTML; // Se espera que esto sea HTML seguro ?>
        </div>
    </div>

    <div class="segundoContenedor">
        <?php echo $contenidoSecundarioHTML; // Se espera que esto sea HTML seguro ?>

        <div class="pie-formulario">
            <?php if (!empty($botonEliminarHTML)): ?>
                <?php echo $botonEliminarHTML; // Se espera que esto sea HTML seguro ?>
            <?php endif; ?>
            <button type="submit" class="btnN icono verde">
                <?php echo icon($iconoBotonSubmit); ?> <?php echo htmlspecialchars($textoBotonSubmit); ?>
            </button>
        </div>
    </div>
</form>
