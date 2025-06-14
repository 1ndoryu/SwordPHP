@extends('layouts.app')

@section('contenido')
<div style="width: 100%; max-width: 500px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2>{{ $titulo ?? 'Iniciar Sesión' }}</h2>

    {{-- Bloque para mostrar mensajes de éxito (ej: tras registro) o de error (ej: credenciales incorrectas) --}}
    @if (isset($exito) && $exito)
    <p style="color: #4F8A10; background-color: #DFF2BF; padding: 10px; border-radius: 4px;">
        {{ $exito }}
    </p>
    @endif
    @if (isset($error) && $error)
    <p style="color: #D8000C; background-color: #FFBABA; padding: 10px; border-radius: 4px;">
        {{ $error }}
    </p>
    @endif

    <form action="/login" method="POST">
        {{-- Campo oculto para el token CSRF, esencial para la seguridad --}}
        <input type="hidden" name="_token" value="{{ session('_token') }}">

        <div style="margin-bottom: 15px;">
            <label for="identificador">Correo Electrónico o Usuario:</label><br>
            <input type="text" id="identificador" name="identificador" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="clave">Contraseña:</label><br>
            <input type="password" id="clave" name="clave" required style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" style="width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Iniciar Sesión
        </button>
    </form>
</div>
@endsection
