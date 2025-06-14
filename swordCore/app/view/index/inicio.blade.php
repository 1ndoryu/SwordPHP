@extends('layouts.app')

@section('titulo', 'Página de Inicio')

@section('contenido')
<div style="padding-top: 50px;">
    <h1>SwordPHP</h1>
    <p>Un framework PHP minimalista, rápido y flexible.</p>
    <hr>
    <div>
        <p>
            <strong>Estado de la Base de Datos:</strong> {{ $estadoConexion }}
        </p>

    </div>
</div>
@endsection
