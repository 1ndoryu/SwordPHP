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

    // 1. Comprobar si el banner está activado en los ajustes.
    if (!obtenerOpcionPlugin($slugPlugin, 'banner_activo', true)) {
        return; // No hacer nada si el banner está desactivado.
    }

    // 2. Obtener las opciones del banner.
    $textoBanner = obtenerOpcionPlugin($slugPlugin, 'texto_banner', '🔌 ¡Hola desde el Plugin de Ejemplo!');
    $posicion = obtenerOpcionPlugin($slugPlugin, 'banner_posicion', 'bottom-right');
    $estilosCSS = obtenerOpcionPlugin($slugPlugin, 'banner_estilos_css', 'background: #007bff; color: white;');

    // 3. Construir los estilos en línea basados en la posición.
    $posicionEstilos = '';
    switch ($posicion) {
        case 'bottom-left':
            $posicionEstilos = 'bottom: 10px; left: 10px;';
            break;
        case 'top-right':
            $posicionEstilos = 'top: 10px; right: 10px;';
            break;
        case 'top-left':
            $posicionEstilos = 'top: 10px; left: 10px;';
            break;
        case 'bottom-right':
        default:
            $posicionEstilos = 'bottom: 10px; right: 10px;';
            break;
    }
    
    // 4. Imprimir el HTML. Saneamos los valores antes de imprimirlos.
    $estilosFinales = 'position: fixed; ' . $posicionEstilos . ' padding: 15px; border-radius: 5px; font-family: sans-serif; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2); ' . htmlspecialchars($estilosCSS);
    
    echo '<div style="' . $estilosFinales . '">';
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
 * Ahora gestiona el guardado de varias opciones y usa el helper de formularios estandarizado.
 *
 * @return string El HTML de la página.
 */
function miPluginEjemplo_renderizarPagina()
{
    $slugPlugin = 'plugin-ejemplo';
    $mensajeExito = '';

    // Definimos los nombres de las opciones para iterar sobre ellas.
    $nombresOpciones = ['texto_banner', 'banner_activo', 'banner_posicion', 'banner_estilos_css'];

    // Si el formulario ha sido enviado, guardamos todos los datos.
    if (request()->method() === 'POST') {
        foreach ($nombresOpciones as $nombreOpcion) {
            // Los checkboxes no enviados no estarán en el POST, así que les damos un valor por defecto de '0'.
            $valor = request()->post($nombreOpcion, ($nombreOpcion === 'banner_activo' ? '0' : ''));
            guardarOpcionPlugin($slugPlugin, $nombreOpcion, $valor);
        }
        $mensajeExito = 'Ajustes guardados correctamente.';
    }

    // Obtenemos los valores actuales de todas las opciones para mostrarlos en los campos.
    $valorTextoBanner = obtenerOpcionPlugin($slugPlugin, 'texto_banner', '🔌 ¡Hola desde el Plugin de Ejemplo!');
    $valorBannerActivo = (bool) obtenerOpcionPlugin($slugPlugin, 'banner_activo', true);
    $valorBannerPosicion = obtenerOpcionPlugin($slugPlugin, 'banner_posicion', 'bottom-right');
    $valorBannerEstilos = obtenerOpcionPlugin($slugPlugin, 'banner_estilos_css', 'background: #007bff; color: white;');

    // Definimos los campos para el formulario en un array.
    $campos = [
        [
            'tipo' => 'checkbox',
            'name' => 'banner_activo',
            'label' => 'Activar Banner',
            'estaMarcado' => $valorBannerActivo,
            'descripcion' => 'Marca esta casilla para mostrar el banner en el pie de página.'
        ],
        [
            'tipo' => 'text',
            'name' => 'texto_banner',
            'label' => 'Texto del Banner',
            'value' => $valorTextoBanner,
            'placeholder' => 'Introduce un texto...'
        ],
        [
            'tipo' => 'select',
            'name' => 'banner_posicion',
            'label' => 'Posición del Banner',
            'value' => $valorBannerPosicion,
            'opciones' => [
                'bottom-right' => 'Abajo a la Derecha',
                'bottom-left' => 'Abajo a la Izquierda',
                'top-right' => 'Arriba a la Derecha',
                'top-left' => 'Arriba a la Izquierda',
            ],
            'descripcion' => 'Elige en qué esquina de la pantalla aparecerá el banner.'
        ],
        [
            'tipo' => 'textarea',
            'name' => 'banner_estilos_css',
            'label' => 'Estilos CSS Personalizados',
            'value' => $valorBannerEstilos,
            'atributos' => ['rows' => 3],
            'placeholder' => 'ej: background: #ff0000; color: white;',
            'descripcion' => 'Añade CSS personalizado para el contenedor del banner (sin las etiquetas <style>).'
        ],
    ];

    // Renderizamos el formulario completo usando el helper.
    return renderizarFormularioAjustesPlugin([
        'campos' => $campos,
        'mensajeExito' => $mensajeExito,
        'descripcionFormulario' => 'Desde aquí puedes configurar las opciones del plugin de ejemplo.',
        'textoBoton' => 'Guardar Todos los Ajustes'
    ]);
}

/**
 * 6. Registrar los shortcodes del plugin.
 */
function miPluginEjemplo_registrarShortcodes()
{
    // Shortcode simple: [saludo]
    agregarShortcode('saludo', 'miPluginEjemplo_callbackSaludo');

    // Shortcode con atributos: [saludo_personalizado nombre="Gemini"]
    agregarShortcode('saludo_personalizado', 'miPluginEjemplo_callbackSaludoPersonalizado');
    
    // Shortcode que envuelve contenido: [caja borde="blue"]Este es el contenido[/caja]
    agregarShortcode('caja', 'miPluginEjemplo_callbackCaja');
}
// Ejecutar la función de registro de shortcodes.
miPluginEjemplo_registrarShortcodes();

/**
 * Función de callback para el shortcode [saludo].
 *
 * @param array $atributos Atributos del shortcode (no usado aquí).
 * @param string|null $contenido Contenido envuelto por el shortcode (no usado aquí).
 * @return string El HTML de reemplazo.
 */
function miPluginEjemplo_callbackSaludo($atributos, $contenido = null)
{
    return "<strong>¡Hola desde el shortcode de ejemplo!</strong>";
}

/**
 * Función de callback para el shortcode [saludo_personalizado].
 *
 * @param array $atributos Atributos del shortcode.
 * @param string|null $contenido Contenido envuelto por el shortcode (no usado aquí).
 * @return string El HTML de reemplazo.
 */
function miPluginEjemplo_callbackSaludoPersonalizado($atributos, $contenido = null)
{
    // Establecemos un valor por defecto para el atributo 'nombre'.
    $nombre = $atributos['nombre'] ?? 'Mundo';
    return "¡Hola, " . htmlspecialchars($nombre) . "!";
}

/**
 * Función de callback para el shortcode [caja].
 *
 * @param array $atributos Atributos del shortcode.
 * @param string|null $contenido Contenido envuelto por el shortcode.
 * @return string El HTML de reemplazo.
 */
function miPluginEjemplo_callbackCaja($atributos, $contenido = null)
{
    if (is_null($contenido)) {
        return '';
    }
    // Permitimos personalizar el color del borde con un atributo.
    $colorBorde = isset($atributos['borde']) ? htmlspecialchars($atributos['borde']) : '#ccc';
    $estilos = "border: 1px solid {$colorBorde}; padding: 15px; margin: 1em 0; border-radius: 4px; background-color: #f9f9f9;";
    
    // Se procesan shortcodes anidados dentro del contenido.
    $contenidoProcesado = procesarShortcodes($contenido);

    return "<div style='{$estilos}'>{$contenidoProcesado}</div>";
}