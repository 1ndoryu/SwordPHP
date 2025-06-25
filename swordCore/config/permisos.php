<?php

return [
    'api' => [
        'admin' => [
            'manage_options',
            'manage_content',
            'manage_users',
        ],
        'editor' => [
            'create_content',
            'edit_own_content',
            'delete_own_content',
        ],
        'autor' => [
            'create_content',
            'edit_own_content',
            'delete_own_content',
        ],
        'colaborador' => [
            'create_content',
            'edit_own_content',
        ],
        'suscriptor' => [
            'like_content',
            'comment_content',
        ],
        'anonimo' => [],
    ],
    'tipos_contenido' => [
        'admin' => ['pagina', 'sample', 'comentario'],
        'editor' => ['pagina', 'sample', 'comentario'],
        'autor' => ['sample'],
        'colaborador' => ['sample'],
        'suscriptor' => [],
        'anonimo' => [],
    ],
];