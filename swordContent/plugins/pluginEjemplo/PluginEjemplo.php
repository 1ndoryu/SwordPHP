<?php

/**
 * Plugin Name: Plugin de Ejemplo
 * Plugin URI: https://swordphp.com/
 * Description: Un plugin de ejemplo para demostrar el sistema de hooks (acciones y filtros).
 * Version: 1.0
 * Author: SwordPHP Team
 * Author URI: https://swordphp.com/
 */

// Asegurarse de que el plugin no se pueda acceder directamente.
if (!defined('SWORD_CORE_PATH')) {
    exit;
}

/**
 * 1. Usar una acción para añadir contenido al pie de página.
 */
agregarAccion('pieDePagina', 'miPluginEjemplo_agregarContenidoFooter');

function miPluginEjemplo_agregarContenidoFooter()
{
    echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #007bff; color: white; padding: 15px; border-radius: 5px; font-family: sans-serif; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">';
    echo '🔌 ¡Hola desde el Plugin de Ejemplo! (Acción: pieDePagina)';
    echo '</div>';
}

/**
 * 2. Usar un filtro para modificar el título de la página.
 */
agregarFiltro('elTitulo', 'miPluginEjemplo_modificarTitulo', 10, 2);

function miPluginEjemplo_modificarTitulo($tituloActual, $pagina)
{
    $nuevoTitulo = "[Plugin] " . $tituloActual;
    return $nuevoTitulo;
}

/**
 * 3. Añadir el enlace al menú del panel de administración.
 */
agregarFiltro('menuLateralAdmin', 'miPluginEjemplo_agregarMenuAdmin');

function miPluginEjemplo_agregarMenuAdmin($menuItems)
{
    $menuItems['plugin_ejemplo_settings'] = [
        'url' => '/panel/ajustes/plugin-ejemplo',
        'text' => 'Ajustes del Plugin',
    ];
    return $menuItems;
}

/**
 * 4. Registrar la página de ajustes del plugin.
 */
function miPluginEjemplo_registrarPagina()
{
    agregarPaginaAdmin(
        'plugin-ejemplo', // El slug debe coincidir con el de la URL
        [
            'page_title' => 'Ajustes del Plugin Ejemplo',
            'callback' => 'miPluginEjemplo_renderizarPagina',
        ]
    );
}
miPluginEjemplo_registrarPagina();


/**
 * 5. Función que se encarga de renderizar el HTML de la página de ajustes.
 *
 * @return string El HTML de la página.
 */
function miPluginEjemplo_renderizarPagina()
{
    // Por ahora, el formulario no guarda nada, eso lo haremos en el siguiente paso.
    $html = '
        <div class="formulario-contenedor" style="flex: 1; max-width: none;">
            <div class="cuerpo-formulario">
                <p>Desde aquí puedes configurar el comportamiento del plugin.</p>
                <hr>
                <form method="POST" action="">
                    ' . csrf_field() . '
                    <div class="grupo-formulario">
                        <label for="opcion_ejemplo"><strong>Texto del Banner</strong></label>
                        <input type="text" id="opcion_ejemplo" name="opcion_ejemplo" value="Valor de ejemplo" placeholder="Introduce un texto...">
                        <small>Este texto se podría mostrar en el banner del pie de página.</small>
                    </div>

                    <div class="pie-formulario" style="justify-content: flex-start;">
                        <button type="submit" class="btnN">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    ';
    return $html;
}
