@extends('layouts.admin')

{{-- 1. Se usa la sección 'tituloPagina' correcta --}}
@section('tituloPagina', 'Gestión de Páginas')

{{-- 2. Se usa la sección 'contenidoPanel' correcta --}}
@section('contenidoPanel')

{{-- Se elimina la estructura de 'card' de AdminLTE --}}
<div class="vista-listado">

    <div class="cabecera-vista">
        {{-- El H1 ya está en el layout principal, aquí podemos poner una descripción si se quiere --}}
        {{-- <h3>Listado de todas las páginas</h3> --}}
        <div class="acciones-vista">
            <a href="/panel/paginas/create" class="btn-crear">
                Crear Nueva Página
            </a>
        </div>
    </div>

    {{-- Bloque para mostrar mensajes de éxito o error --}}
    @if (session('success'))
        <div class="alerta alerta-exito" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alerta alerta-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="contenido-vista">
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th style="width: 10px">ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Estado</th>
                    <th>Fecha de Creación</th>
                    <th style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paginas as $pagina)
                    <tr>
                        <td>{{ $pagina->id }}</td>
                        <td>{{ $pagina->titulo }}</td>
                        <td>{{ $pagina->autor->nombre ?? 'N/A' }}</td>
                        <td>
                            {{-- Clases genéricas para los badges de estado --}}
                            @if ($pagina->estado == 'publicado')
                                <span class="badge badge-publicado">Publicado</span>
                            @else
                                <span class="badge badge-borrador">Borrador</span>
                            @endif
                        </td>
                        <td>{{ $pagina->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="/panel/paginas/edit/{{ $pagina->id }}" class="btn-editar">
                                Editar
                            </a>
                            
                            <form action="/panel/paginas/destroy/{{ $pagina->id }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta página?');">
                                @csrf
                                <button type="submit" class="btn-eliminar">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No se encontraron páginas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- La paginación de Laravel no depende de un framework CSS --}}
        <div class="paginacion">
            {{ $paginas->links() }}
        </div>
    </div>
</div>

@endsection