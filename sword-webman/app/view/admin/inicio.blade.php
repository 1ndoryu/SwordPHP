@extends('layouts.admin')

{{-- Define el título que aparecerá en la cabecera de la página del panel --}}
@section('tituloPagina', 'Dashboard')

{{-- El contenido específico de esta página se inyecta en la sección 'contenidoPanel' del layout,
     evitando el conflicto con la sección 'contenido' del layout padre. --}}
@section('contenidoPanel')
<div style="background-color: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h3>¡Bienvenido al Panel de Administración de SwordPHP!</h3>
    <p>
        Esta es la página principal de tu panel. Desde aquí, podrás empezar a gestionar todo el contenido de tu sitio.
    </p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">
    <p>
        Siguiendo nuestra hoja de ruta, los próximos grandes pasos son:
    </p>
    <ul>
        <li>Implementar el CRUD (Crear, Leer, Actualizar, Eliminar) para las páginas o entradas.</li>
        <li>Desarrollar el sistema de ruteo para mostrar ese contenido en el frontend.</li>
        <li>Mejorar la gestión de usuarios.</li>
    </ul>
</div>
@endsection