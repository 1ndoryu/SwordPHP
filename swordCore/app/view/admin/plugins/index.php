<?php
// El título '$tituloPagina' y las demás variables son pasadas desde el PluginController.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
            <?php /* Botón para añadir nuevos plugins (ej. subir zip) podría ir aquí en el futuro */ ?>
        </div>
    </div>

    <?php // Mostrar mensajes de éxito o error de la sesión
    if (!empty($mensajeExito)): ?>
        <div class="alerta alertaExito" role="alert">
            <?php echo htmlspecialchars($mensajeExito); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($mensajeError)): ?>
        <div class="alerta alertaError" role="alert">
            <?php echo htmlspecialchars($mensajeError); ?>
        </div>
    <?php endif; ?>

    <div class="contenidoVista">
        <div class="listaContenido">
            <?php if (!empty($plugins)): ?>
                <?php foreach ($plugins as $slug => $plugin): ?>
                    <?php $esActivo = in_array($slug, $pluginsActivos ?? []); ?>
                    <div class="contenidoCard <?php echo $esActivo ? 'activo' : ''; ?>">
                        <div class="contenidoInfo">

                            <div class="infoItem iconoB iconoG">
                                <?php echo icon('bookClose'); // Icono temporal, se puede crear uno específico para plugins. 
                                ?>
                            </div>

                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($plugin['nombre']); ?></span>
                                <?php if (!empty($plugin['descripcion'])): ?>
                                    <small class="descripcion-tema oculto"><?php echo htmlspecialchars($plugin['descripcion']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($plugin['autor'] ?: 'Anónimo'); ?></span>
                            </div>

                            <div class="infoItem">
                                <span class="badge badgeVersion">
                                    v<?php echo htmlspecialchars($plugin['version'] ?: 'N/A'); ?>
                                </span>
                            </div>

                            <div class="infoItem">
                                <?php if ($esActivo): ?>
                                    <span class="badge badgePublicado">Activo</span>
                                <?php else: ?>
                                    <span class="badge badgeBorrador">Inactivo</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="contenidoAcciones themes">
                            <?php if ($esActivo): ?>
                                <form action="/panel/plugins/desactivar/<?php echo htmlspecialchars($slug); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btnN desactivado" style="background-color: #fce8e6; color: #a50e0e; border-color: #f8c8c4;">Desactivar</button>
                                </form>
                            <?php else: ?>
                                <form action="/panel/plugins/activar/<?php echo htmlspecialchars($slug); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btnN">Activar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron plugins. Puedes añadir nuevos en el directorio <code>swordContent/plugins</code>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>