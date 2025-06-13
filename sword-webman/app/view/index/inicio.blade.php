{{-- Heredamos toda la estructura HTML de nuestro layout base --}}
@extends('layouts.app')

{{-- Definimos el título específico para esta página --}}
@section('titulo', 'Página de Inicio')

{{-- Esta es la sección de contenido principal que se inyectará en el layout --}}
@section('contenido')
<div style="text-align: center; padding: 50px;">
    <h1>¡Bienvenido a Tu Nuevo Proyecto!</h1>
    <p>Esta página está siendo renderizada con el motor de plantillas Blade.</p>
    <p>Hemos conectado exitosamente un layout base con una vista hija.</p>
</div>
@endsection
