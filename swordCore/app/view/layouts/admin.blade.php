@extends('layouts.app')

@section('estilos')
    @parent
    @php
        assetService()->encolarDirectorio('/css/panel', 'css');
    @endphp
@endsection

@section('scripts')
    @parent
    @php
        assetService()->encolarDirectorio('/js/panel', 'js');
    @endphp
@endsection

{{-- Define el título que aparecerá en la pestaña del navegador --}}
@section('titulo')
@yield('tituloPagina', 'Panel de Administración') | SwordPHP
@endsection

{{-- Agregamos estilos básicos para el layout del panel --}}
@section('estilos')

@endsection

@section('contenido')
<div class="panelContenedor">
    <aside class="panelSidebar">
        <div class="panelSidebarHeader">
            SwordPHP
        </div>
        <nav class="panelSidebarNav">
            <ul>

                <li><a href="/panel" class="{{ request()->path() == 'panel' ? 'activo' : '' }}">Dashboard</a></li>


                <li><a href="/panel/paginas" class="{{ str_starts_with(request()->path(), 'panel/paginas') ? 'activo' : '' }}">Páginas</a></li>

                <li><a href="/panel/usuarios" class="{{ str_starts_with(request()->path(), 'panel/usuarios') ? 'activo' : '' }}">Usuarios</a></li>

                <li><a href="/panel/ajustes" class="{{ request()->path() == 'panel/ajustes' ? 'activo' : '' }}">Ajustes</a></li>


            </ul>
        </nav>
    </aside>

    <main class="panelContenidoPrincipal">
        <header class="panelContenidoCabecera">
            <h1>@yield('tituloPagina', 'Dashboard')</h1>

            @php($usuario = usuarioActual())
            @if ($usuario)
            <div class="infoUsuario">
                <span>Hola, {{ $usuario->nombremostrado ?: $usuario->nombreusuario }}</span>
                <a href="/logout" class="logoutBtn">Cerrar Sesión</a>
            </div>
            @endif
        </header>

        {{-- El contenido específico de cada página del panel se insertará aquí --}}
        <div class="contenidoPagina">
            {{-- CAMBIO IMPORTANTE: La sección de contenido se llama 'contenidoPanel' --}}
            @yield('contenidoPanel')
        </div>
    </main>
</div>
@endsection

{{-- Se elimina la sección de scripts si no se usa, para que no interfiera con el layout base. --}}
@section('scripts')
@endsection
