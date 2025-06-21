<?php

/**
 * 1. Definir una página que el sistema debe crear y mantener.
 *
 * El sistema se asegurará de que una página con el slug de URL 'politica-de-privacidad'
 * siempre exista en la base de datos.
 */
swDefinirPagina('definicion-pagina-privacidad', [
    'titulo'    => 'Política de Privacidad',
    'slug'      => 'politica-de-privacidad', // La URL será /politica-de-privacidad
    'contenido' => '<h2>1. Introducción</h2><p>Este contenido fue generado desde el código del tema y puede ser restaurado en cualquier momento.</p>'
]);

/**
 * 2. Encolar los recursos (CSS y JS) del tema.
 *
 */
encolarRecursos('asset/css/main.css');
encolarRecursos('asset/js');
