@extends('layouts.admin')

{{-- Usamos las secciones correctas del layout del panel --}}
@section('tituloPagina', 'Crear Nueva Página')

@section('contenidoPanel')

<div class="formulario-contenedor">

    <div class="cabecera-formulario">
        <p>Rellena los campos para crear una nueva página</p>
        <a href="/panel/paginas" class="btn-volver">
            &larr; Volver al listado
        </a>
    </div>

    <form action="/panel/paginas/store" method="POST">
        @csrf
        <div class="cuerpo-formulario">

            {{-- Bloque para mostrar mensajes de error de validación --}}
            @if ($errors->any())
                <div class="alerta alerta-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Campo para el Título --}}
            <div class="grupo-formulario">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Introduce el título" value="{{ old('titulo') }}" required>
            </div>
            
            {{-- Asumo que 'subtitulo' es una columna en la tabla 'paginas' --}}
            <div class="grupo-formulario">
                <label for="subtitulo">Subtítulo (Opcional)</label>
                <input type="text" id="subtitulo" name="subtitulo" placeholder="Introduce el subtítulo" value="{{ old('subtitulo') }}">
            </div>

            {{-- CAMPO DE METADATOS DE EJEMPLO --}}
            <div class="grupo-formulario">
                <label for="meta_autor_invitado">Autor Invitado (Metadato)</label>
                <input type="text" id="meta_autor_invitado" name="meta[autor_invitado]" placeholder="Ej: Dr. Juan Pérez" value="{{ old('meta.autor_invitado') }}">
                <small>Este campo se guarda en la tabla de metadatos.</small>
            </div>

            {{-- Campo para el Contenido --}}
            <div class="grupo-formulario">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" placeholder="Escribe el contenido de la página aquí...">{{ old('contenido') }}</textarea>
            </div>

            {{-- Campo para el Estado --}}
            <div class="grupo-formulario">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="borrador" {{ old('estado', 'borrador') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="publicado" {{ old('estado') == 'publicado' ? 'selected' : '' }}>Publicado</option>
                </select>
            </div>

        </div>

        <div class="pie-formulario">
            <button type="submit" class="btn-principal">Crear Página</button>
            <a href="/panel/paginas" class="btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

@endsection