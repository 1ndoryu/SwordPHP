<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Mi Proyecto Webman')</title>

    {{-- Aquí irán los estilos CSS en el futuro --}}
    @yield('estilos')
</head>

<body>

    <header>
        {{-- Aquí podría ir una barra de navegación común --}}
    </header>

    <main>
        {{-- El contenido principal de cada página se insertará aquí --}}
        @yield('contenido')
    </main>

    <footer>
        {{-- Aquí podría ir un pie de página común --}}
    </footer>

    {{-- Aquí irán los scripts de JavaScript en el futuro --}}
    @yield('scripts')
</body>

</html>