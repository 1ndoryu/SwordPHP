<?php

namespace App\controller;

use App\service\OpcionService;
use App\service\PaginaService;
use support\Request;
use support\Response;
use DateTimeZone;

class AjustesController
{
    private PaginaService $paginaService;
    private OpcionService $opcionService;

    public function __construct(PaginaService $paginaService, OpcionService $opcionService)
    {
        $this->paginaService = $paginaService;
        $this->opcionService = $opcionService;
    }

    /**
     * Muestra la página unificada de ajustes (Generales y de Lectura).
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // --- Datos para Ajustes de Lectura ---
        $paginasPublicadas = $this->paginaService->obtenerPaginasPublicadas();
        $paginaInicioActualSlug = $this->opcionService->obtenerOpcion('pagina_de_inicio_slug');

        // --- Datos para Ajustes Generales ---
        $opcionesGenerales = [
            'titulo_sitio'          => $this->opcionService->obtenerOpcion('titulo_sitio', 'SwordPHP'),
            'descripcion_sitio'     => $this->opcionService->obtenerOpcion('descripcion_sitio', 'Otro sitio increíble con SwordPHP'),
            'disuadir_motores_busqueda' => (bool) $this->opcionService->obtenerOpcion('disuadir_motores_busqueda', 0),
            'formato_fecha'         => $this->opcionService->obtenerOpcion('formato_fecha', 'd/m/Y'),
            'formato_hora'          => $this->opcionService->obtenerOpcion('formato_hora', 'H:i:s'),
            'zona_horaria'          => $this->opcionService->obtenerOpcion('zona_horaria', 'UTC'),
            'favicon_url'           => $this->opcionService->obtenerOpcion('favicon_url', ''),
            'correo_administrador'  => $this->opcionService->obtenerOpcion('correo_administrador', idUsuarioActual() ? usuarioActual()->correoelectronico : ''),
            'permitir_registros'    => (bool) $this->opcionService->obtenerOpcion('permitir_registros', 0)
        ];
        $zonasHorarias = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        // --- Mensajes y Vista ---
        $mensajeExito = $request->session()->pull('mensaje_exito');

        return view('admin/ajustes/index', [
            'tituloPagina'          => 'Ajustes',
            'paginas'               => $paginasPublicadas,
            'paginaInicioActual'    => $paginaInicioActualSlug,
            'opciones'              => $opcionesGenerales,
            'zonasHorarias'         => $zonasHorarias,
            'mensajeExito'          => $mensajeExito
        ]);
    }

    /**
     * Guarda todos los ajustes (Generales y de Lectura).
     *
     * @param Request $request
     * @return Response
     */
    public function guardar(Request $request): Response
    {
        // --- Guardar Ajustes de Lectura ---
        $slugPaginaInicio = $request->post('pagina_inicio');
        $this->opcionService->guardarOpcion('pagina_de_inicio_slug', $slugPaginaInicio);

        // --- Guardar Ajustes Generales ---
        $opcionesDeTexto = [
            'titulo_sitio',
            'descripcion_sitio',
            'formato_fecha',
            'formato_hora',
            'zona_horaria',
            'favicon_url',
            'correo_administrador',
        ];

        foreach ($opcionesDeTexto as $clave) {
            $this->opcionService->guardarOpcion($clave, $request->post($clave, ''));
        }

        // Manejo especial para los checkboxes
        $valorDisuadir = $request->post('disuadir_motores_busqueda') ? '1' : '0';
        $this->opcionService->guardarOpcion('disuadir_motores_busqueda', $valorDisuadir);

        $valorRegistros = $request->post('permitir_registros') ? '1' : '0';
        $this->opcionService->guardarOpcion('permitir_registros', $valorRegistros);

        // --- Mensaje y Redirección ---
        $request->session()->set('mensaje_exito', 'Ajustes guardados correctamente.');
        return redirect('/panel/ajustes');
    }
}