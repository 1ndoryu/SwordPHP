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
 *
 * Esta función se engancha a la acción 'pieDePagina' que añadimos en el footer.php.
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
 *
 * Esta función se engancha al filtro 'elTitulo' que añadimos en pagina.php.
 * Acepta 2 argumentos: el título a modificar y el objeto de la página actual.
 */
agregarFiltro('elTitulo', 'miPluginEjemplo_modificarTitulo', 10, 2);

function miPluginEjemplo_modificarTitulo($tituloActual, $pagina)
{
    // Añadimos un prefijo al título original.
    $nuevoTitulo = "[Plugin] " . $tituloActual;

    // Devolvemos el título modificado para que sea mostrado.
    return $nuevoTitulo;
}
