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
 * Ahora usa el valor guardado en las opciones.
 */
agregarAccion('pieDePagina', 'miPluginEjemplo_agregarContenidoFooter');

function miPluginEjemplo_agregarContenidoFooter()
{
    $slugPlugin = 'plugin-ejemplo';
    $nombreOpcion = 'texto_banner';
    // Obtenemos la opción guardada, con un valor por defecto si no existe.
    $textoBanner = obtenerOpcionPlugin($slugPlugin, $nombreOpcion, '🔌 ¡Hola desde el Plugin de Ejemplo! (Valor por defecto)');

    echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #007bff; color: white; padding: 15px; border-radius: 5px; font-family: sans-serif; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">';
    // Saneamos el valor ANTES de imprimirlo en el HTML.
    echo htmlspecialchars($textoBanner);
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
 * Ahora también gestiona el guardado de los datos y usa el helper de formularios.
 *
 * @return string El HTML de la página.
 */
function miPluginEjemplo_renderizarPagina()
{
    $slugPlugin = 'plugin-ejemplo';
    $nombreOpcion = 'texto_banner';
    $mensajeExito = '';

    // Si el formulario ha sido enviado, guardamos los datos.
    if (request()->method() === 'POST') {
        $valorOpcion = request()->post($nombreOpcion, '');
        guardarOpcionPlugin($slugPlugin, $nombreOpcion, $valorOpcion);
        $mensajeExito = '<div class="alerta alertaExito" style="margin-bottom: 1rem;">Ajustes guardados correctamente.</div>';
    }

    // Obtenemos el valor actual de la opción para mostrarlo en el campo.
    $valorActual = obtenerOpcionPlugin($slugPlugin, $nombreOpcion, 'Valor de ejemplo');

    // Usamos el nuevo helper para renderizar el campo de texto.
    $campoTexto = renderFormPlugin([
        'tipo' => 'text',
        'name' => $nombreOpcion,
        'label' => 'Texto del Banner',
        'value' => $valorActual,
        'placeholder' => 'Introduce un texto...',
        'descripcion' => 'Este texto se mostrará en el banner del pie de página.'
    ]);

    // Construimos el HTML final del formulario.
    $html = '
        <div class="formulario-contenedor" style="flex: 1; max-width: none;">
            <div class="cuerpo-formulario">
                ' . $mensajeExito . '
                <p>Desde aquí puedes configurar el comportamiento del plugin.</p>
                <hr>
                <form method="POST" action="">
                    ' . csrf_field() . '
                    ' . $campoTexto . '
                    <div class="pie-formulario" style="justify-content: flex-start;">
                        <button type="submit" class="btnN">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    ';
    return $html;
}
