<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('translation.locale', 'es')) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') - Panel de Administración</title>

    {{-- Estilos básicos para una apariencia limpia y funcional --}}
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            background-color: #f4f6f9;
            color: #333;
        }

        .panel-layout {
            display: flex;
            min-height: 100vh;
        }

        .panel-sidebar {
            width: 240px;
            background-color: #343a40;
            color: #fff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .panel-sidebar-header {
            padding: 1.2rem 1rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid #495057;
        }

        .panel-sidebar-nav ul {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }

        .panel-sidebar-nav a {
            color: #c2c7d0;
            text-decoration: none;
            display: block;
            padding: 0.8rem 1.5rem;
        }

        .panel-sidebar-nav a:hover,
        .panel-sidebar-nav a.activo {
            color: #fff;
            background-color: #495057;
        }

        .panel-contenido-principal {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .panel-cabecera {
            background-color: #fff;
            padding: 1rem 2rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-cuerpo {
            padding: 2rem;
        }

    </style>

    @yield('estilosAdicionales')
</head>
<body>

    <div class="panel-layout">
        <aside class="panel-sidebar">
            <div class="panel-sidebar-header">
                SwordPHP
            </div>
            <nav class="panel-sidebar-nav">
                <ul>
                    <li><a href="/admin" class="activo">Dashboard</a></li>
                    {{-- Futuros enlaces del menú: Páginas, Usuarios, Ajustes, etc. --}}
                </ul>
            </nav>
        </aside>

        <div class="panel-contenido-principal">
            <header class="panel-cabecera">
                <h2>@yield('tituloPagina', 'Dashboard')</h2>
                <div>
                    <span>Usuario: NombreUsuario</span>
                    <a href="/logout" style="margin-left: 15px;">Cerrar Sesión</a>
                </div>
            </header>

            <main class="panel-cuerpo">
                @yield('contenido')
            </main>
        </div>
    </div>

    @yield('scriptsAdicionales')

</body>
</html>
