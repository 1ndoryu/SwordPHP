@extends('layouts.app')

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
                <li><a href="/panel" class="activo">Dashboard</a></li>
                {{-- Futuros enlaces del menú: Páginas, Usuarios, Ajustes, etc. --}}
            </ul>
        </nav>
    </aside>

    <main class="panelContenidoPrincipal">
        <header class="panelContenidoCabecera">
            <h1>@yield('tituloPagina', 'Dashboard')</h1>
            
            {{--
              CAMBIO: Usamos la función usuarioActual() para mostrar info del usuario.
              Obtenemos el objeto del usuario y mostramos su nombre.
            --}}
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
            @yield('contenidoPanel')
        </div>
    </main>
</div>
@endsection

{{-- Se elimina la sección de scripts si no se usa, para que no interfiera con el layout base. --}}
@section('scripts')
@endsection