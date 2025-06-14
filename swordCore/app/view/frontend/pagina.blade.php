@extends('layouts.app')

{{-- El título de la pestaña del navegador será el título de la página --}}
@section('titulo', $pagina->titulo)

@section('contenido')
<div style="max-width: 800px; margin: 40px auto; padding: 20px; background-color: #fff; border: 1px solid #ddd; border-radius: 8px;">
    
    {{-- Título principal de la página --}}
    <h1 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">{{ $pagina->titulo }}</h1>

    {{-- Si existe un subtítulo, lo mostramos --}}
    @if($pagina->subtitulo)
        <h2 style="color: #666; font-weight: 300;">{{ $pagina->subtitulo }}</h2>
        <p> usando swordCore\app\view\frontend\pagina.blade.php </p>
    @endif

    <div style="margin: 20px 0;"></div>

    {{-- Contenido principal de la página.
         Usamos {!! !!} para renderizar HTML que pueda estar guardado
         en la base de datos (por ejemplo, desde un editor de texto enriquecido).
         Es crucial que este contenido sea saneado antes de guardarlo para 
         prevenir ataques XSS. --}}
    <div class="contenido-pagina">
        {!! $pagina->contenido !!}
    </div>

    <hr style="margin-top: 30px;">

    <div style="font-size: 0.9em; color: #888; text-align: right;">
        <span>Fecha: {{ $pagina->created_at->format('d/m/Y') }}</span>
    </div>

</div>
@endsection