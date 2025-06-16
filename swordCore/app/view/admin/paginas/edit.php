<?php

// Se define el título de la página
$tituloPagina = 'Editar Página';
// $errorMessage = session()->pull('error'); // Será manejado por formularioGeneral.php

// Se incluye la cabecera del panel de administración.
echo partial('layouts/admin-header', []);

// Preparar datos para camposContenidoPrincipal
$camposPrincipalesHTML = partial(
    'admin/components/camposContenidoPrincipal',
    [
        'tituloValor' => old('titulo', $pagina->titulo ?? ''),
        'subtituloValor' => old('subtitulo', $pagina->subtitulo ?? ''),
        'contenidoValor' => old('contenido', $pagina->contenido ?? ''),
        'incluirSubtitulo' => true
    ]
);

// Preparar datos para el gestor de metadatos
// Asumiendo que $pagina->metas ya tiene el formato correcto para el componente gestor-metadatos
$gestorMetadatosHTML = partial(
    'admin/components/gestor-metadatos',
    ['metadatos' => $pagina->metas ?? []]
);

// Combinar campos principales y gestor de metadatos
$contenidoPrincipalCompletoHTML = $camposPrincipalesHTML . $gestorMetadatosHTML;

// Preparar datos para camposContenidoSecundario
$camposSecundariosHTML = partial(
    'admin/components/camposContenidoSecundario',
    [
        'estadoActual' => old('estado', $pagina->estado ?? 'borrador')
    ]
);

// Preparar HTML para el botón de eliminar (si aplica)
// Este es un ejemplo, la URL y el método exacto para eliminar podrían variar.
// Se asumirá que hay una ruta /panel/paginas/delete/{id} y usa el método DELETE.
$botonEliminarHTML = '';
if (isset($pagina) && isset($pagina->id)) {
    // Es importante generar un formulario separado para la eliminación si se usa un método que no sea GET/POST directo,
    // o manejarlo con JavaScript. Por simplicidad aquí, se usa un enlace estilizado como botón,
    // pero idealmente sería un form con método DELETE.
    // O, si el componente formularioGeneral lo soportara, un botón con action específica.
    // Por ahora, se dejará como un botón simple que podría necesitar JS para funcionar con DELETE.
    // O se podría modificar formularioGeneral para aceptar múltiples botones.
    // Para este paso, lo mantenemos simple y podría ser un enlace directo si la app lo maneja vía GET (no recomendado para delete)
    // o un botón que active un modal de confirmación y luego un form JS.
    // Aquí simulamos un botón que visualmente está ahí.
    // IMPORTANTE: La acción de borrado real necesitaría un form POST con _method=DELETE o JS.
    // $botonEliminarHTML = "<button formaction='/panel/paginas/delete/" . htmlspecialchars($pagina->id) . "' formmethod='POST' name='_method' value='DELETE' class='btnN icono rojo' onclick='return confirm("¿Estás seguro de que quieres eliminar esta página?");'>" . icon('borrar') . " Eliminar</button>";
    // Simplificando para que encaje en el formularioGeneral actual:
     $botonEliminarHTML = "<a href='/panel/paginas/delete/" . htmlspecialchars($pagina->id ?? '') . "' class='btnN icono rojo' onclick='if(confirm("¿Estás seguro de que quieres eliminar esta página?")) { event.preventDefault(); document.getElementById("delete-form-{$pagina->id}").submit(); } else { event.preventDefault(); }'>" . icon('borrar') . "</a>";
     $botonEliminarHTML .= "<form id='delete-form-".htmlspecialchars($pagina->id ?? '')."' action='/panel/paginas/delete/" . htmlspecialchars($pagina->id ?? '') . "' method='POST' style='display: none;'>" . csrf_field() . "<input type='hidden' name='_method' value='DELETE'></form>";
}


// Renderizar el formulario general
echo partial(
    'admin/components/formularioGeneral',
    [
        'actionUrl' => '/panel/paginas/update/%s', // %s será reemplazado por el id
        'id' => $pagina->id ?? '',
        'method' => 'POST', // El formulario HTML será POST, _method se usa para PUT/PATCH
        '_method_actual' => 'PUT', // Campo oculto _method para simular PUT
        'tituloFormulario' => 'Editando "Página": <strong>' . htmlspecialchars($pagina->titulo ?? '') . '</strong>',
        'textoBotonVolver' => 'Volver al listado',
        'urlVolver' => '/panel/paginas',
        'textoBotonSubmit' => 'Actualizar Página',
        // 'iconoBotonSubmit' => 'checkCircle', // Usará el valor por defecto
        'contenidoPrincipalHTML' => $contenidoPrincipalCompletoHTML,
        'contenidoSecundarioHTML' => $camposSecundariosHTML,
        'botonEliminarHTML' => $botonEliminarHTML
        // 'mensajeError' => $errorMessage // Se maneja dentro de formularioGeneral
    ]
);

// Se incluye el pie de página del panel de administración.
echo partial('layouts/admin-footer', []);
?>