<?php
// Here is your custom functions.

/**
 * Renderiza una vista dentro de un layout.
 *
 * @param string $vistaContenido La vista principal a renderizar (ej: 'index/index').
 * @param array $datos Los datos para la vista de contenido.
 * @param array $datosLayout Los datos para la vista del layout (ej: ['titulo' => 'Mi Título']).
 * @param string $vistaLayout La vista del layout a usar.
 * @return \support\Response
 */
function renderConLayout(string $vistaContenido, array $datos = [], array $datosLayout = [], string $vistaLayout = 'layout/app')
{
    // 1. Renderiza la vista del contenido y obtiene su HTML como string.
    $contenido = view($vistaContenido, $datos)->rawBody();

    // 2. Combina los datos del layout con el contenido renderizado.
    $datosFinales = array_merge($datosLayout, [
        'contenido' => $contenido
    ]);

    // 3. Renderiza el layout con los datos finales y devuelve la respuesta completa.
    return view($vistaLayout, $datosFinales);
}