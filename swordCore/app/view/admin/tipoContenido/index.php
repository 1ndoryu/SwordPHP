<?php
// 1. Define las variables y el título usando la configuración genérica.
$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['name'] ?? 'Contenidos Genéricos');

// 2. Incluye la cabecera del panel.
echo partial('layouts/admin-header', []);
?>

<div class="vistaListado">

    <div class="cabeceraVista">
        <div class="accionesVista">
            <a href="/panel/<?= $slug ?>/crear" class="btnCrear">
                Añadir
            </a>
        </div>
    </div>

    <?php // Bloque para mostrar mensajes de éxito o error (copiado de la vista de páginas) 
    ?>
    <?php if (session()->has('success')): ?>
        <div class="alerta alertaExito" role="alert">
            <?php echo htmlspecialchars(session('success')); ?>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alerta alertaError" role="alert">
            <?php echo htmlspecialchars(session('error')); ?>
        </div>
    <?php endif; ?>

    <div class="contenidoVista">

        <div class="listaContenido">
            <?php
            // Se comprueba si la colección de entradas no está vacía.
            // Se usa la variable $entradas en lugar de $paginas.
            if (!empty($entradas)):
                foreach ($entradas as $entrada):
            ?>
                    <div class="contenidoCard">
                        <div class="contenidoInfo">
                            <div class="infoItem iconoB iconoG">
                                <?php echo icon('file'); // O puedes usar icon('file') como en páginas 
                                ?>
                            </div>
                            <div class="infoItem infoTitulo">
                                <span><?php echo htmlspecialchars($entrada->titulo); ?></span>
                            </div>
                            <div class="infoItem" style="display: none">
                                <span><?php echo htmlspecialchars($entrada->id); ?></span>
                            </div>
                            <div class="infoItem">
                                <span class="badge badgeBorrador"><?php echo htmlspecialchars($entrada->slug); ?></span>
                            </div>
                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($entrada->estado); ?></span>
                            </div>



                            <div class="infoItem">
                                <span><?php echo htmlspecialchars($entrada->created_at->format('d/m/Y H:i')); ?></span>
                            </div>


                        </div>

                        <div class="contenidoAcciones">
                            <a href="/panel/<?= $slug ?>/editar/<?php echo htmlspecialchars($entrada->id); ?>" class="iconoB btnEditar">
                                <?php echo icon('edit'); ?>
                            </a>

                            <form action="/panel/<?= $slug ?>/eliminar/<?php echo htmlspecialchars($entrada->id); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta entrada?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="iconoB IconoRojo btnEliminar">
                                    <?php echo icon('borrar'); ?>
                                </button>
                            </form>
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
        // Se asume que podrías pasar variables de paginación también a esta vista.
        // Si no usas paginación aquí, puedes eliminar este bloque.
        if (isset($paginaActual) && isset($totalPaginas)):
        ?>
            <div class="paginacion">
                <?php echo renderizarPaginacion($paginaActual, $totalPaginas); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
?>

<?php
// 3. Incluye el pie de página para cerrar la estructura.
echo partial('layouts/admin-footer', []);
?>