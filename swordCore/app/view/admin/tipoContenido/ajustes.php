<?php

/**
 * Vista para los ajustes de un Tipo de Contenido Personalizado.
 *
 * Variables disponibles desde TipoContenidoController:
 * @var string $tituloPagina
 * @var string $slug El slug del tipo de contenido actual.
 * @var array $config La configuración del tipo de contenido.
 * @var string|null $mensajeExito Mensaje de éxito.
 * @var array $plantillasDisponibles Plantillas de página disponibles en el tema.
 * @var array $ajustesGuardados Ajustes guardados para este tipo de contenido.
 */

echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Ajustes']);
?>

<form method="POST" action="/panel/<?php echo htmlspecialchars($slug); ?>/ajustes" class="formulario-ajustes">
    <?php echo csrf_field(); ?>

    <div class="ajustesSword">

        <?php if (!empty($mensajeExito)): ?>
            <div class="alerta alertaExito">
                <?php echo htmlspecialchars($mensajeExito); ?>
            </div>
        <?php endif; ?>

        <div class="bloque card">
            <div class="card-header">
                <h3 class="card-title">Ajustes de Plantilla</h3>
            </div>
            <div class="card-body">
                <div class="grupo-formulario opcionFormAjuste">
                    <label for="plantilla_single">Plantilla para la vista individual</label>
                    <select name="plantilla_single" id="plantilla_single">
                        <option value="">Plantilla por defecto del tema (jerarquía)</option>
                        <?php if (!empty($plantillasDisponibles)): ?>
                            <?php foreach ($plantillasDisponibles as $archivo => $nombre): ?>
                                <option value="<?php echo htmlspecialchars($archivo); ?>"
                                    <?php if (isset($ajustesGuardados['plantilla_single']) && $ajustesGuardados['plantilla_single'] === $archivo) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($nombre); ?> (<?php echo htmlspecialchars($archivo); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small>Elige el archivo de plantilla que se usará para mostrar una entrada individual de "<?php echo htmlspecialchars($config['labels']['singular_name'] ?? $slug); ?>".</small>
                </div>
            </div>
        </div>

        <div class="bloque card">
            <div class="card-header">
                <h3 class="card-title">Ajustes de Permisos</h3>
            </div>
            <div class="card-body">
                <p style="opacity: 0.7;">(Próximamente) Aquí podrás definir qué roles de usuario tienen permiso para ver el contenido de este tipo.</p>
            </div>
        </div>

        <div class="bloque card">
            <button type="submit" class="btnN btn-primario">Guardar todos los cambios</button>
        </div>

    </div>

</form>

<style>
    /* Estilos basados en la página de ajustes generales para consistencia visual */
    .formulario-ajustes {
        display: flex;
        gap: 20px;
    }

    .ajustesSword {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .card {
        padding: var(--padding-general);
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .card-header .card-title {
        margin: 0;
        font-size: 1.2em;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .grupo-formulario small {
        opacity: 0.7;
        font-size: 0.9em;
        margin-top: 4px;
    }

    .grupo-formulario.opcionFormAjuste {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
</style>

<?php
echo partial('layouts/admin-footer', []);
?>