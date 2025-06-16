<?php

$tituloPagina = 'Crear Nueva Página';
// $errorMessage = session()->pull('error'); // Será manejado por formularioGeneral.php

echo partial('layouts/admin-header', []);

// Preparar datos para camposContenidoPrincipal
$camposPrincipalesHTML = partial(
    'admin/components/camposContenidoPrincipal',
    [
        'tituloValor' => old('titulo', ''),
        'subtituloValor' => old('subtitulo', ''),
        'contenidoValor' => old('contenido', ''),
        'incluirSubtitulo' => true
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
        'actionUrl' => '/panel/paginas/store',
        'method' => 'POST',
        'tituloFormulario' => 'Rellena los campos para crear una nueva página',
        'textoBotonVolver' => 'Volver al listado',
        'urlVolver' => '/panel/paginas',
        'textoBotonSubmit' => 'Crear Página',
        // 'iconoBotonSubmit' => 'checkCircle', // Usará el valor por defecto
        'contenidoPrincipalHTML' => $contenidoPrincipalCompletoHTML,
        'contenidoSecundarioHTML' => $camposSecundariosHTML,
        // 'mensajeError' => $errorMessage // Se maneja dentro de formularioGeneral
    ]
);

echo partial('layouts/admin-footer', []);
?>