{{-- Heredamos toda la estructura HTML de nuestro layout base --}}
@extends('layouts.app')

{{-- Definimos el título específico para esta página --}}
@section('titulo', 'Página de Inicio')

{{-- Esta es la sección de contenido principal que se inyectará en el layout --}}
@section('contenido')
<div style="padding-top: 50px;">
    <h1>SwordPHP</h1>
    <p>Un framework PHP minimalista, rápido y flexible.</p>
    <hr>
    <div>
        <p>
            <strong>Estado de la Base de Datos:</strong> {{ $estadoConexion }}
        </p>
        <p>
            <strong>Tiempo de respuesta del servidor:</strong> {{ $tiempoCarga }} ms
        </p>
    </div>
</div>
@endsection
