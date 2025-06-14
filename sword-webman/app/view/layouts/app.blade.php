<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('translation.locale', 'es')) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Mi Proyecto Webman')</title>

    {{-- Aquí irán los estilos CSS específicos de cada página --}}
    @yield('estilos')

    {{-- Imprime los estilos CSS encolados a través del AssetService --}}
    {!! assetService()->imprimirEstilos() !!}
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

    {{-- Aquí irán los scripts de JavaScript específicos de cada página --}}
    @yield('scripts')

    {{-- Imprime los scripts JS encolados a través del AssetService --}}
    {!! assetService()->imprimirScripts() !!}
</body>

</html>