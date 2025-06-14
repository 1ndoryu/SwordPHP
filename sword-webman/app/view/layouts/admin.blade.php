@extends('layouts.app')

{{-- Define el título que aparecerá en la pestaña del navegador --}}
@section('titulo')
    @yield('tituloPagina', 'Panel de Administración') | SwordPHP
@endsection

{{-- Agregamos estilos básicos para el layout del panel --}}
@section('estilos')
<style>
    body {
        background-color: #f4f7f6;
        margin: 0;
        font-family: sans-serif;
    }
    .panel-contenedor {
        display: flex;
        min-height: 100vh;
    }
    .panel-sidebar {
        width: 250px;
        background-color: #2c3e50;
        color: #ecf0f1;
        padding-top: 20px;
        flex-shrink: 0;
    }
    .panel-sidebar-header {
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        padding-bottom: 20px;
    }
    .panel-sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .panel-sidebar-nav a {
        display: block;
        padding: 15px 20px;
        color: #ecf0f1;
        text-decoration: none;
        transition: background-color 0.3s;
    }
    .panel-sidebar-nav a:hover, .panel-sidebar-nav a.activo {
        background-color: #34495e;
    }
    .panel-contenido-principal {
        flex-grow: 1;
        padding: 30px;
    }
    .panel-contenido-cabecera {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .panel-contenido-cabecera h1 {
        margin: 0;
    }
    .info-usuario {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .info-usuario span {
        font-weight: bold;
    }
    .logout-btn {
        display: inline-block;
        padding: 8px 15px;
        background-color: #e74c3c;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    .logout-btn:hover {
        background-color: #c0392b;
    }
</style>
@endsection

@section('contenido')
<div class="panel-contenedor">
    <aside class="panel-sidebar">
        <div class="panel-sidebar-header">
            SwordPHP
        </div>
        <nav class="panel-sidebar-nav">
            <ul>
                <li><a href="/panel" class="activo">Dashboard</a></li>
                {{-- Futuros enlaces del menú: Páginas, Usuarios, Ajustes, etc. --}}
            </ul>
        </nav>
    </aside>

    <main class="panel-contenido-principal">
        <header class="panel-contenido-cabecera">
            <h1>@yield('tituloPagina', 'Dashboard')</h1>
            
            {{--
              CAMBIO: Usamos la función usuarioActual() para mostrar info del usuario.
              Obtenemos el objeto del usuario y mostramos su nombre.
            --}}
            @php($usuario = usuarioActual())
            @if ($usuario)
                <div class="info-usuario">
                    <span>Hola, {{ $usuario->nombremostrado ?: $usuario->nombreusuario }}</span>
                    <a href="/logout" class="logout-btn">Cerrar Sesión</a>
                </div>
            @endif
        </header>

        {{-- El contenido específico de cada página del panel se insertará aquí --}}
        <div class="contenido-pagina">
            @yield('contenidoPanel')
        </div>
    </main>
</div>
@endsection

{{-- Se elimina la sección de scripts si no se usa, para que no interfiera con el layout base. --}}
@section('scripts')
@endsection