@extends('layouts.admin')

@section('tituloPagina', 'Ajustes')


@section('contenidoPanel')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Ajustes Generales</h1>
        </div>
        <div class="card-body">
            
            @if (!empty($mensajeExito))
                <div class="alert alert-success">
                    {{ $mensajeExito }}
                </div>
            @endif

            <form method="POST" action="/panel/ajustes/guardar">
                <div class="form-group mb-3">
                    <label for="pagina_inicio" class="form-label"><strong>Página de inicio</strong></label>
                    <p class="form-text text-muted">Elige qué página se mostrará como portada de tu sitio web. Si seleccionas la opción por defecto, se mostrará la bienvenida del sistema.</p>
                    <select name="pagina_inicio" id="pagina_inicio" class="form-select">
                        <option value="">— Página de bienvenida por defecto —</option>
                        @if (isset($paginas) && !$paginas->isEmpty())
                            @foreach ($paginas as $pagina)
                                <option value="{{ $pagina->slug }}" @if (isset($paginaInicioActual) && $pagina->slug === $paginaInicioActual) selected @endif>
                                    {{ $pagina->titulo }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection