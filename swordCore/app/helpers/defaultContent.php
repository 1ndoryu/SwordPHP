<?php

use App\service\TipoContenidoService;

function registrarTipoContenido(string $slug, array $argumentos)
{
    TipoContenidoService::getInstancia()->registrar($slug, $argumentos);
}

registrarTipoContenido(
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
