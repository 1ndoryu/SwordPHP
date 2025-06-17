<?php
$tituloPagina = 'Inicio';
echo partial('layouts/admin-header', []);
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        align-items: flex-start;
    }

    .dashboard-columna {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .widget-caja {
        background-color: #eeeeee;
        border-radius: 4px;
    }

    .dashboard-grid p {
        color: #000000d4;
    }

    .widget-caja h3.widget-titulo {
        font-size: 16px;
        font-weight: 600;
        padding: 12px 15px;
        margin: 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .widget-caja .widget-contenido {
        padding: 15px;
    }

    .widget-caja .widget-contenido p:first-child {
        margin-top: 0;
    }

    .widget-caja .widget-contenido p:last-child {
        margin-bottom: 0;
    }

    /* Responsive: en pantallas pequeñas, las columnas se apilan */
    @media (max-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
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