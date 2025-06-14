@extends('layouts.admin')

{{-- 1. Se usa la sección 'tituloPagina' correcta --}}
@section('tituloPagina', 'Crear Nueva Página')

{{-- 2. Se usa la sección 'contenidoPanel' correcta --}}
@section('contenidoPanel')

<div class="formulario-contenedor">

    {{-- El H1 ya está en el layout, aquí podemos añadir un botón para volver --}}
    <div class="cabecera-formulario">
        <a href="/panel/paginas" class="btn-volver">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/store" method="POST">
        @csrf
        <div class="cuerpo-formulario">

            {{-- Bloque para mostrar mensajes de error --}}
            @if (session('error'))
                <div class="alerta alerta-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Campo para el Título --}}
            <div class="grupo-formulario">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="{{ session('inputs.titulo', '') }}" required>
            </div>

            {{-- Campo para el Subtítulo --}}
            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="{{ session('inputs.subtitulo', '') }}">
            </div>

            {{-- Campo para el Contenido --}}
            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí...">{{ session('inputs.contenido', '') }}</textarea>
            </div>

            {{-- Campo para el Estado --}}
            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="borrador" {{ session('inputs.estado', 'borrador') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="publicado" {{ session('inputs.estado') == 'publicado' ? 'selected' : '' }}>Publicado</option>
                </select>
            </div>

        </div>

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Guardar Página</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

@endsection