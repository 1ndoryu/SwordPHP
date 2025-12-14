<div class="contenedorTabs">
    <div class="encabezadosTabs">
        <button class="botonTab activo" data-tab="tabInicio">Inicio</button>
        <button class="botonTab" data-tab="tabNovedades">Novedades</button>
        <button class="botonTab" data-tab="tabSistema">Sistema</button>
    </div>

    <div id="tabInicio" class="panelTab activo">
        <div class="widgetsEscritorio">
            <div class="tarjeta">
                <h3>Bienvenido a SwordPHP</h3>
                <p>Este es el nuevo panel de administración construido con PHP puro.</p>
            </div>
            <div class="tarjeta">
                <h3>Resumen Rápido</h3>
                <p>Todo el sistema funcionando correctamente.</p>
            </div>
        </div>
    </div>

    <div id="tabNovedades" class="panelTab">
        <div class="tarjeta">
            <h3>Últimas Novedades</h3>
            <p>Se ha implementado el sistema de Tabs nativo sin dependencias externas.</p>
        </div>
    </div>

    <div id="tabSistema" class="panelTab">
        <div class="widgetsEscritorio">
            <div class="tarjeta">
                <h3>Estado del Sistema</h3>
                <p>PHP Version: <?= phpversion() ?></p>
            </div>
            <div class="tarjeta">
                <h3>Base de Datos</h3>
                <p>Conectado (PostgreSQL)</p>
            </div>
        </div>
    </div>
</div>