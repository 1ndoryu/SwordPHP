{{-- Hereda de la plantilla base --}}
@extends('layouts.app')

{{-- Define el título de la página --}}
@section('titulo', $pagina->titulo)

{{-- Define el contenido de la página --}}
@section('contenido')
<h1>{{ $titulo }}</h1>
<div>
    {!! $pagina->contenido !!}
</div>
<hr>
<p>✅ Vista cargada desde: sword-theme-default</p>
@endsection
