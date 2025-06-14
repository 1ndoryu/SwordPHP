@extends('layouts.app')

@section('tituloPagina', 'Test de AJAX')

@section('contenido')
<h1>Prueba del Sistema AJAX (Sin Temas)</h1>
<p>
    Esta página prueba el sistema AJAX centralizado. La acción está registrada directamente en
    <code>config/bootstrap.php</code> y esta vista se encuentra en <code>app/view/test/ajax.blade.php</code>.
</p>

<div style="margin-top: 20px; padding: 15px; background-color: #eef; border: 1px solid #cce;">
    <input type="text" id="info-input" placeholder="Dato para enviar">
    <button id="btn-ajax-test">Llamar a la acción 'test_sin_tema'</button>

    <h4>Respuesta del Servidor:</h4>
    <pre id="ajax-response" style="background: #333; color: #fff; padding: 15px; white-space: pre-wrap; word-wrap: break-word;">Aquí aparecerá la respuesta...</pre>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#btn-ajax-test').on('click', function() {
            var info = $('#info-input').val();
            $('#ajax-response').text('Cargando...');

            $.ajax({
                url: '/ajax'
                , method: 'POST'
                , dataType: 'json'
                , data: {
                    action: 'test_sin_tema'
                    , info: info
                }
                , success: function(response) {
                    $('#ajax-response').text(JSON.stringify(response, null, 2));
                }
                , error: function(jqXHR, textStatus, errorThrown) {
                    var error_data = {
                        status: jqXHR.status
                        , status_text: jqXHR.statusText
                        , response: jqXHR.responseJSON || 'No se pudo obtener una respuesta JSON.'
                    };
                    $('#ajax-response').text('Error en la llamada AJAX:\n' + JSON.stringify(error_data, null, 2));
                }
            });
        });
    });

</script>
@endsection
