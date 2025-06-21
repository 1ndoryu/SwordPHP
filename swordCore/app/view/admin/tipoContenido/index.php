<?php
// 1. Define las variables y el título usando la configuración genérica.
$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['name'] ?? 'Contenidos Genéricos');

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', ['tituloPagina' => $tituloPagina ?? 'Panel']);
?>

<div class="bloque vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
            <a href="/panel/<?= $slug ?>/crear" class="btnCrear">
                Añadir
            </a>
        </div>
    </div>

    <?php // Mostrar mensajes pasados desde el controlador ?>
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
            // Se comprueba si la colección de entradas no está vacía.
            if (!$entradas->isEmpty()):
                foreach ($entradas as $entrada):
            ?>
                    <div class="contenidoCard">
                        <div class="contenidoInfo">
                            <div class="infoItem iconoB iconoG">
                                <?php echo icon('file'); ?>
                            </div>
                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($entrada->titulo); ?></span>
                            </div>
                            <div class="infoItem" style="display: none">
                                <span><?php echo htmlspecialchars($entrada->id); ?></span>
                            </div>
                            <div class="infoItem">
                                <?php if ($entrada->estado == 'publicado'): ?>
                                    <span class="badge badgePublicado">Publicado</span>
                                <?php else: ?>
                                    <span class="badge badgeBorrador">Borrador</span>
                                <?php endif; ?>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($entrada->created_at->format('d/m/Y H:i')); ?></span>
                            </div>
                        </div>

                        <div class="contenidoAcciones">
                            <a href="<?php echo getPermalinkPost($entrada); ?>" class="iconoB btnVer" target="_blank" title="Ver">
                                <?php echo icon('ver'); ?>
                            </a>
                            <a href="/panel/<?= $slug ?>/editar/<?php echo htmlspecialchars($entrada->id); ?>" class="iconoB btnEditar" title="Editar">
                                <?php echo icon('edit'); ?>
                            </a>
                            <button type="button" class="iconoB IconoRojo btnEliminar" title="Eliminar" onclick="eliminarRecurso('/panel/<?= $slug ?>/eliminar/<?php echo htmlspecialchars($entrada->id); ?>', '<?php echo csrf_token(); ?>', '¿Estás seguro de que deseas eliminar esta entrada?');">
                                <?php echo icon('borrar'); ?>
                            </button>
                        </div>
                    </div>
                <?php
                endforeach;
            else: // Si no hay entradas que mostrar
                ?>
                <div class="alerta alertaInfo" style="text-align: center;">
                    No se encontraron <?= htmlspecialchars(strtolower($labels['name'] ?? 'contenidos')) ?>.
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Ahora el controlador siempre pasa estas variables, por lo que la paginación funcionará.
        if (isset($paginaActual) && isset($totalPaginas)):
        ?>
            <div class="paginacion">
                <?php echo renderizarPaginacion($paginaActual, $totalPaginas, "/panel/$slug"); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- ?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>