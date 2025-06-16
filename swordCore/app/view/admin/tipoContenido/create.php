<?php

$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['add_new_item'] ?? 'Añadir Nuevo');
// $errorMessage = session()->pull('error'); // Será manejado por formularioGeneral.php

echo partial('layouts/admin-header', []);

// Preparar datos para camposContenidoPrincipal
// No se incluye subtítulo para tipoContenido estándar
$camposPrincipalesHTML = partial(
    'admin/components/camposContenidoPrincipal',
    [
        'tituloValor' => old('titulo', ''),
        'contenidoValor' => old('contenido', ''),
        'incluirSubtitulo' => false // Clave aquí
    ]
);

// Preparar datos para el gestor de metadatos
$old_meta_array = old('meta', []);
$metadatos_para_componente = collect($old_meta_array)->map(function ($item) {
    return (object) [
        'meta_key' => $item['clave'] ?? null,
        'meta_value' => $item['valor'] ?? null,
    ];
});
$gestorMetadatosHTML = partial(
    'admin/components/gestor-metadatos',
    ['metadatos' => $metadatos_para_componente]
);

// Combinar campos principales y gestor de metadatos
$contenidoPrincipalCompletoHTML = $camposPrincipalesHTML . $gestorMetadatosHTML;

// Preparar datos para camposContenidoSecundario
$camposSecundariosHTML = partial(
    'admin/components/camposContenidoSecundario',
    [
        'estadoActual' => old('estado', 'borrador')
    ]
);

// Renderizar el formulario general
echo partial(
    'admin/components/formularioGeneral',
    [
        'actionUrl' => '/panel/' . ($slug ?? '') . '/crear',
        'method' => 'POST',
        'tituloFormulario' => 'Rellena los campos para crear una nueva entrada de "' . htmlspecialchars($labels['singular_name'] ?? 'Contenido') . '"',
        'textoBotonVolver' => 'Volver al listado',
        'urlVolver' => '/panel/' . ($slug ?? ''),
        'textoBotonSubmit' => htmlspecialchars($labels['add_new_item'] ?? 'Crear Entrada'),
        'contenidoPrincipalHTML' => $contenidoPrincipalCompletoHTML,
        'contenidoSecundarioHTML' => $camposSecundariosHTML,
    ]
);

echo partial('layouts/admin-footer', []);
?>