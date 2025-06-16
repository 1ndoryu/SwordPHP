<?php

$labels = $config['labels'];
$tituloPagina = htmlspecialchars($labels['edit_item'] ?? 'Editar Entrada');
// $errorMessage = session()->pull('error'); // Será manejado por formularioGeneral.php

echo partial('layouts/admin-header', []);

// Preparar datos para camposContenidoPrincipal
$camposPrincipalesHTML = partial(
    'admin/components/camposContenidoPrincipal',
    [
        'tituloValor' => old('titulo', $entrada->titulo ?? ''),
        'contenidoValor' => old('contenido', $entrada->contenido ?? ''),
        'incluirSubtitulo' => false // No subtítulo para tipoContenido genérico
    ]
);

// Preparar datos para el gestor de metadatos
$gestorMetadatosHTML = partial(
    'admin/components/gestor-metadatos',
    ['metadatos' => $entrada->metas ?? []] // Asumiendo que $entrada->metas tiene el formato correcto
);

// Combinar campos principales y gestor de metadatos
$contenidoPrincipalCompletoHTML = $camposPrincipalesHTML . $gestorMetadatosHTML;

// Preparar datos para camposContenidoSecundario
$camposSecundariosHTML = partial(
    'admin/components/camposContenidoSecundario',
    [
        'estadoActual' => old('estado', $entrada->estado ?? 'borrador')
    ]
);

// Preparar HTML para el botón de eliminar
$botonEliminarHTML = '';
if (isset($entrada) && isset($entrada->id) && isset($slug)) {
    $urlEliminar = "/panel/" . htmlspecialchars($slug) . "/eliminar/" . htmlspecialchars($entrada->id);
    $formIdEliminar = "delete-form-" . htmlspecialchars($slug) . "-" . htmlspecialchars($entrada->id);

    $botonEliminarHTML = "<a href='" . $urlEliminar . "' class='btnN icono rojo' onclick='if(confirm("¿Estás seguro de que quieres eliminar esta entrada?")) { event.preventDefault(); document.getElementById("" . $formIdEliminar . "").submit(); } else { event.preventDefault(); }'>" . icon('borrar') . "</a>";
    $botonEliminarHTML .= "<form id='" . $formIdEliminar . "' action='" . $urlEliminar . "' method='POST' style='display: none;'>" . csrf_field() . "<input type='hidden' name='_method' value='DELETE'></form>";
}

// Renderizar el formulario general
echo partial(
    'admin/components/formularioGeneral',
    [
        'actionUrl' => '/panel/' . ($slug ?? '') . '/editar/%s', // %s será reemplazado por el id de la entrada
        'id' => $entrada->id ?? '',
        'method' => 'POST', // Formulario HTML usa POST
        '_method_actual' => 'PUT', // Para que se envíe como PUT
        'tituloFormulario' => 'Editando "' . htmlspecialchars($labels['singular_name'] ?? 'Entrada') . '": <strong>' . htmlspecialchars($entrada->titulo ?? '') . '</strong>',
        'textoBotonVolver' => 'Volver al listado',
        'urlVolver' => '/panel/' . ($slug ?? ''),
        'textoBotonSubmit' => htmlspecialchars($labels['update_item'] ?? 'Actualizar Entrada'),
        'contenidoPrincipalHTML' => $contenidoPrincipalCompletoHTML,
        'contenidoSecundarioHTML' => $camposSecundariosHTML,
        'botonEliminarHTML' => $botonEliminarHTML
    ]
);

echo partial('layouts/admin-footer', []);
?>