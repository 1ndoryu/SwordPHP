@extends('layouts.admin')

@section('tituloPagina', $titulo)

@section('contenidoPanel')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $titulo }}</h3>
        <div class="card-tools">
            <a href="/panel/usuarios/crear" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir Nuevo Usuario
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped projects">
            <thead>
                <tr>
                    <th style="width: 1%">#</th>
                    <th style="width: 30%">Nombre de Usuario</th>
                    <th>Correo Electrónico</th>
                    <th>Rol</th>
                    <th>Miembro desde</th>
                    <th style="width: 20%"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>
                        <a>{{ $usuario->nombremostrado ?: $usuario->nombreusuario }}</a>
                        <br>
                        <small>Último acceso: {{ $usuario->updated_at->diffForHumans() }}</small>
                    </td>
                    <td>{{ $usuario->correoelectronico }}</td>
                    <td>
                        @if($usuario->rol === 'admin')
                            <span class="badge badge-success">Administrador</span>
                        @else
                            <span class="badge badge-info">{{ ucfirst($usuario->rol) }}</span>
                        @endif
                    </td>
                    <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                    <td class="project-actions text-right">
                        <a class="btn btn-info btn-sm" href="/panel/usuarios/editar/{{ $usuario->id }}">
                            <i class="fas fa-pencil-alt"></i> Editar
                        </a>
                        <button class="btn btn-danger btn-sm" data-id="{{ $usuario->id }}" data-url="/panel/usuarios/eliminar">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        @if ($usuarios->hasPages())
            {{ $usuarios->links('vendor.pagination.bootstrap-4') }}
        @endif
    </div>
</div>
@endsection