<?php
// 1. Define el título de la página.
$tituloPagina = 'Gestión de Temas';

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
             </div>
    </div>

    <?php // Mensajes de éxito o error, usando las mismas variables que en la vista de páginas ?>
    <?php if (!empty($successMessage)): ?>
        <div class="alerta alertaExito" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="alerta alertaError" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <div class="contenidoVista">

        <div class="listaContenido">
            <?php
            // Se comprueba si el array de temas no está vacío.
            if (!empty($temas)):
                foreach ($temas as $slug => $tema):
                    $esActivo = ($slug === $temaActivo);
            ?>
                    <div class="contenidoCard <?php echo $esActivo ? 'activo' : ''; ?>">
                        <div class="contenidoInfo">
                            
                            <div class="infoItem iconoB iconoG">
                                <?php echo icon('bookClose'); // Un ícono apropiado para temas ?>
                            </div>

                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($tema['nombre']); ?></span>
                                <small class="descripcion-tema oculto"><?php echo htmlspecialchars($tema['descripcion'] ?: 'Sin descripción.'); ?></small>
                            </div>

                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($tema['autor'] ?: 'Anónimo'); ?></span>
                            </div>

                            <div class="infoItem">
                                <span class="badge badgeVersion">
                                    v<?php echo htmlspecialchars($tema['version'] ?: 'N/A'); ?>
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
                                <span class="btnN desactivado">Activado</span>
                            <?php else: ?>
                                <form action="/panel/temas/activar/<?php echo htmlspecialchars($slug); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btnN">Activar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                endforeach;
            else: // Si no hay temas que mostrar
                ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron temas en el directorio <code>swordContent/themes</code>.
                </div>
            <?php endif; ?>
        </div>

        <?php // La paginación no se incluye ya que los temas no suelen paginarse. ?>

    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>