@extends('layouts.app')

@section('contenido')
    {{-- Estilo simple para el formulario de registro --}}
    <div style="width: 100%; max-width: 500px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
        <h2>{{ $titulo ?? 'Registro' }}</h2>

        {{-- Bloque para mostrar mensajes de error o éxito que enviamos desde el controlador --}}
        @if (session('error'))
            <p style="color: #D8000C; background-color: #FFBABA; padding: 10px; border-radius: 4px;">
                {{ session('error') }}
            </p>
        @endif
        @if (session('exito'))
            <p style="color: #4F8A10; background-color: #DFF2BF; padding: 10px; border-radius: 4px;">
                {{ session('exito') }}
            </p>
        @endif

        <form action="/registro" method="POST">
            {{--
               CAMBIO: Reemplazamos @csrf (específico de Laravel)
               por el campo oculto manual que usa el token de la sesión de Webman.
            --}}
            <input type="hidden" name="_token" value="{{ session('_token') }}">

            <div style="margin-bottom: 15px;">
                <label for="nombreUsuario">Nombre de Usuario:</label><br>
                <input type="text" id="nombreUsuario" name="nombreUsuario" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="correoElectronico">Correo Electrónico:</label><br>
                <input type="email" id="correoElectronico" name="correoElectronico" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="clave">Contraseña:</label><br>
                <input type="password" id="clave" name="clave" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="nombreMostrado">Nombre a Mostrar (Opcional):</label><br>
                <input type="text" id="nombreMostrado" name="nombreMostrado" style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" style="width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Crear Cuenta
            </button>
        </form>
    </div>
@endsection