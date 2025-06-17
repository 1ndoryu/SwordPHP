<?php

/**
 * Vista para la página unificada de Ajustes.
 *
 * Variables disponibles desde AjustesController:
 * @var string $tituloPagina
 * @var string|null $mensajeExito
 * @var array $opciones Array con todos los ajustes generales.
 * @var array $zonasHorarias Array con todas las zonas horarias disponibles.
 * @var \Illuminate\Support\Collection $paginas Colección de páginas publicadas.
 * @var string|null $paginaInicioActual Slug de la página de inicio actual.
 */

echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Ajustes']);
?>

<form method="POST" action="/panel/ajustes/guardar" class="formulario-ajustes">
    <?php echo csrf_field(); ?>

    <div class="ajustesSword">

        <?php // Muestra un mensaje de éxito si existe en la sesión. 
        ?>
        <?php if (!empty($mensajeExito)): ?>
            <div class="alerta alertaExito">
                <?php echo htmlspecialchars($mensajeExito); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ajustes Generales</h3>
            </div>
            <div class="card-body">
                <div class="grupo-formulario">
                    <label for="titulo_sitio">Título del sitio</label>
                    <input type="text" name="titulo_sitio" id="titulo_sitio" value="<?= htmlspecialchars($opciones['titulo_sitio']) ?>">
                </div>

                <div class="grupo-formulario">
                    <label for="descripcion_sitio">Descripción corta</label>
                    <input type="text" name="descripcion_sitio" id="descripcion_sitio" value="<?= htmlspecialchars($opciones['descripcion_sitio']) ?>">
                    <small>En pocas palabras, explica de qué va este sitio.</small>
                </div>

                <div class="grupo-formulario">
                    <label for="correo_administrador">Dirección de correo electrónico de administración</label>
                    <input type="email" name="correo_administrador" id="correo_administrador" value="<?= htmlspecialchars($opciones['correo_administrador']) ?>">
                    <small>Esta dirección se utiliza para fines de administración, como la notificación de nuevos usuarios.</small>
                </div>

                <div class="grupo-formulario-checkbox">
                    <label class="checkboxAjusteSw">
                        <input type="checkbox" name="permitir_registros" value="1" <?php if ($opciones['permitir_registros']) echo 'checked'; ?>>
                        Miembros: Cualquiera puede registrarse.
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Formato de Fecha y Hora</h3>
            </div>
            <div class="card-body">
                <div class="grupo-formulario opcionFormAjuste">
                    <label for="zona_horaria">Zona horaria</label>
                    <select name="zona_horaria" id="zona_horaria">
                        <?php foreach ($zonasHorarias as $zona): ?>
                            <option value="<?= htmlspecialchars($zona) ?>" <?= ($opciones['zona_horaria'] === $zona) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($zona) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="formato_fecha">Formato de fecha</label>
                    <input type="text" name="formato_fecha" id="formato_fecha" value="<?= htmlspecialchars($opciones['formato_fecha']) ?>">
                    <small>Puedes usar los caracteres de formato de fecha de PHP. Ej: <code>d/m/Y</code> para 17/06/2025</small>
                </div>

                <div class="grupo-formulario">
                    <label for="formato_hora">Formato de hora</label>
                    <input type="text" name="formato_hora" id="formato_hora" value="<?= htmlspecialchars($opciones['formato_hora']) ?>">
                    <small>Ej: <code>H:i:s</code> para 10:45:38</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ajustes de Lectura</h3>
            </div>
            <div class="card-body">
                <div class="grupo-formulario opcionFormAjuste">
                    <label for="pagina_inicio">Página de inicio</label>
                    <select name="pagina_inicio" id="pagina_inicio">
                        <option value="">— Página de bienvenida por defecto —</option>
                        <?php if (isset($paginas) && !$paginas->isEmpty()): ?>
                            <?php foreach ($paginas as $pagina): ?>
                                <option value="<?= htmlspecialchars($pagina->slug) ?>" <?= ($pagina->slug === $paginaInicioActual) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pagina->titulo) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small>Elige qué página mostrar como portada de tu sitio.</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ajustes Varios</h3>
            </div>
            <div class="card-body">
                <div class="grupo-formulario">
                    <label for="favicon_url">URL del Favicon</label>
                    <input type="text" name="favicon_url" id="favicon_url" value="<?= htmlspecialchars($opciones['favicon_url']) ?>" placeholder="https://ejemplo.com/favicon.ico">
                </div>

                <div class="grupo-formulario-checkbox">
                    <label for="disuadir_motores_busqueda">Visibilidad en buscadores</label>
                    <label class="checkboxAjusteSw">
                        <input type="checkbox" name="disuadir_motores_busqueda" id="disuadir_motores_busqueda" value="1" <?php if ($opciones['disuadir_motores_busqueda']) echo 'checked'; ?>>
                        Disuadir a los motores de búsqueda de indexar este sitio.
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <button type="submit" class="btnN btn-primario">Guardar todos los cambios</button>
        </div>

    </div>

    <div class="ajustes-columna-lateral" style="display: none;">



    </div>

</form>

<style>
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

    .ajustes-columna-lateral {
        width: 300px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .card {
        padding: var(--padding-general);
        background: var(--color-fondo-contenido);
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .card-header {}

    .card-title {
        margin: 0;
        font-size: 1.2em;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .card-publicar {
        padding: 15px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .card-publicar .btnN {
        width: 100%;
    }

    .grupo-formulario small {
        opacity: 0.7;
        font-size: 0.9em;
        margin-top: 4px;
    }

    .grupo-formulario-checkbox {
        display: flex;
        flex-direction: column;
    }

    .grupo-formulario-checkbox label:first-child {
        font-weight: bold;
        margin-bottom: 5px;
    }

    label.checkboxAjusteSw {
        display: flex;
        flex-direction: row-reverse;
        align-items: center;
        justify-content: flex-end;
    }

    .checkboxAjusteSw input[type="checkbox"] {
        width: fit-content;
    }

    .grupo-formulario-checkbox label:first-child {
        font-weight: bold;
        margin-bottom: 5px;
        gap: 10px;
        color: #000000d4;
    }
</style>

<?php
echo partial('layouts/admin-footer', []);
?>