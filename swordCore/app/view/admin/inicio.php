<?php
$tituloPagina = 'Inicio';
echo partial('layouts/admin-header', []);
?>

<div class="containerInicioAdmin">
    <div class="dashboard-grid">
        <div class="dashboard-columna" id="columna-1">
            <?php foreach ($widgetsColumna1 as $widget): ?>
                <div class="widget-caja" id="widget-<?php echo htmlspecialchars($widget['id']); ?>">
                    <h3 class="widget-titulo"><?php echo htmlspecialchars($widget['titulo']); ?></h3>
                    <div class="widget-contenido">
                        <?php
                        // Ejecutamos el callback del widget para renderizar su contenido.
                        call_user_func($widget['callback']);
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="dashboard-columna" id="columna-2">
            <?php foreach ($widgetsColumna2 as $widget): ?>
                <div class="widget-caja" id="widget-<?php echo htmlspecialchars($widget['id']); ?>">
                    <h3 class="widget-titulo"><?php echo htmlspecialchars($widget['titulo']); ?></h3>
                    <div class="widget-contenido">
                        <?php
                        // Ejecutamos el callback del widget para renderizar su contenido.
                        call_user_func($widget['callback']);
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
echo partial('layouts/admin-footer', []);
?>