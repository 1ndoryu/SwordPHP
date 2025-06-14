{{-- Hereda de la plantilla base --}}
@extends('layouts.app')

{{-- Define el título de la página --}}
@section('titulo', $pagina->titulo)

{{-- Define el contenido de la página --}}
@section('contenido')
    <div class="container mx-auto mt-10 px-4">
        <article>
            <h1 class="text-4xl font-bold mb-4">{{ $pagina->titulo }}</h1>

            <div class="text-gray-600 mb-6">
                {{-- CAMBIO: Usamos optional() y el operador '??' para manejar autores nulos --}}
                <span>el {{ $pagina->created_at->format('d/m/Y') }}</span>
            </div>

            <div class="prose lg:prose-xl">
                {!! $pagina->contenido !!}
            </div>
        </article>

        <div class="mt-8 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
            <p class="font-bold">Nota del Tema</p>
            <p>✅ Vista cargada desde: sword-theme-default</p>
        </div>
    </div>
@endsection