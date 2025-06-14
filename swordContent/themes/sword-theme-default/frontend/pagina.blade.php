@extends('layouts.app')

@section('titulo', $pagina->titulo)

@section('contenido')
    <div class="container mx-auto px-4 py-8">
        
        {{-- ESTE MENSAJE CONFIRMA QUE LA VISTA SE CARGA DESDE EL TEMA --}}
        <h2 class="text-2xl text-green-500 bg-gray-100 p-4 mb-4 border border-green-300 rounded">
            ✅ Vista cargada desde: sword-theme-default
        </h2>
        {{-- FIN DEL MENSAJE DE CONFIRMACIÓN --}}

        <h1 class="text-4xl font-bold mb-4">{{ $pagina->titulo }}</h1>
        <div class="prose lg:prose-xl">
            {!! $pagina->contenido !!}
        </div>
    </div>
@endsection