@extends('layouts.admin')

{{-- Define el título que aparecerá en la cabecera de la página del panel --}}
@section('tituloPagina', 'Dashboard')

{{-- El contenido específico de esta página se inyecta en la sección 'contenidoPanel' del layout,
     evitando el conflicto con la sección 'contenido' del layout padre. --}}
@section('contenidoPanel')
<div style="background-color: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h3>Sword</h3>
    <p>
        Pagina del panel.
    </p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">
</div>
@endsection