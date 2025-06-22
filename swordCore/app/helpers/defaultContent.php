<?php

use App\service\TipoContenidoService;

function definePostType(string $slug, array $argumentos)
{
    TipoContenidoService::getInstancia()->registrar($slug, $argumentos);
}

definePostType(
    'entradas', 
    [
        'labels' => [
            'name'               => 'Entradas',
            'singular_name'      => 'Entrada',
            'add_new_item'       => 'Añadir nueva entrada',
            'edit_item'          => 'Editar entrada',
            'new_item'           => 'Nueva entrada',
            'view_item'          => 'Ver entrada',
            'search_items'       => 'Buscar entradas',
            'not_found'          => 'No se encontraron entradas',
            'not_found_in_trash' => 'No se encontraron entradas en la papelera',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'fa-solid fa-briefcase',
        'supports'     => ['title', 'editor'],
    ]
);

definePostType(
    'samples', 
    [
        'labels' => [
            'name'               => 'Samples',
            'singular_name'      => 'Sample',
            'add_new_item'       => 'Añadir nuevo sample',
            'edit_item'          => 'Editar sample',
            'new_item'           => 'Nuevo sample',
            'view_item'          => 'Ver sample',
            'search_items'       => 'Buscar samples',
            'not_found'          => 'No se encontraron samples',
            'not_found_in_trash' => 'No se encontraron samples en la papelera',
        ],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'fa-solid fa-music',
        'supports'     => ['title', 'editor'],
    ]
);

