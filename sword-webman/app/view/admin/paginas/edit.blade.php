@extends('layouts.admin')

{{-- 1. Se usa la sección 'tituloPagina' correcta --}}
@section('tituloPagina', 'Editar Página')

{{-- 2. Se usa la sección 'contenidoPanel' correcta --}}
@section('contenidoPanel')

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
         {{-- El H1 ya lo pone el layout, aquí mostramos el título que se está editando --}}
         <p>Editando: <strong>{{ $pagina->titulo }}</strong></p>
        <a href="/panel/paginas" class="btn-volver">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/update/{{ $pagina->id }}" method="POST">
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
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="{{ old('titulo', $pagina->titulo) }}" required>
            </div>

            {{-- Campo para el Subtítulo --}}
            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="{{ old('subtitulo', $pagina->subtitulo) }}">
            </div>

            {{-- Campo para el Contenido --}}
            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí...">{{ old('contenido', $pagina->contenido) }}</textarea>
            </div>

            {{-- Campo para el Estado --}}
            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    @php
                        $estadoActual = old('estado', $pagina->estado);
                    @endphp
                    <option value="borrador" {{ $estadoActual == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="publicado" {{ $estadoActual == 'publicado' ? 'selected' : '' }}>Publicado</option>
                </select>
            </div>

        </div>

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Guardar Cambios</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

@endsection