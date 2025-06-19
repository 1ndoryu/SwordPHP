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

    /**
     * Muestra la página de ajustes de enlaces permanentes.
     *
     * @param Request $request
     * @return Response
     */
    public function enlacesPermanentes(Request $request): Response
    {
        $mensajeExito = $request->session()->pull('mensaje_exito');

        // Obtener la estructura actual, con '/%slug%/' como valor por defecto.
        $estructuraActual = $this->opcionService->obtenerOpcion('permalink_structure', '/%slug%/');

        // Determinar si la estructura guardada es una de las predefinidas o una personalizada.
        $valorInputPersonalizado = $estructuraActual;
        $estructurasPredefinidas = [
            '/%slug%/',
            '/%año%/%mes%/%dia%/%slug%/',
            '/%año%/%mes%/%slug%/',
            '/archivos/%id%/'
        ];

        // Si la estructura actual no es una de las predefinidas, se considera personalizada.
        $esPersonalizada = !in_array($estructuraActual, $estructurasPredefinidas);

        return view('admin/ajustes/enlaces-permanentes', [
            'tituloPagina' => 'Enlaces Permanentes',
            'mensajeExito' => $mensajeExito,
            'estructuraActual' => $estructuraActual,
            'valorInputPersonalizado' => $valorInputPersonalizado,
            'esPersonalizada' => $esPersonalizada
        ]);
    }

    /**
     * Guarda la configuración de enlaces permanentes.
     *
     * @param Request $request
     * @return Response
     */
    public function guardarEnlacesPermanentes(Request $request): Response
    {
        // 1. Determinar la estructura final a guardar
        $estructuraSeleccionada = $request->post('permalink_structure');
        $estructuraAGuardar = '';

        if ($estructuraSeleccionada === 'custom') {
            // Si es personalizada, tomamos el valor del campo de texto.
            $estructuraAGuardar = $request->post('custom_structure', '/%slug%/');
        } else {
            // Si es una opción predefinida, usamos su valor.
            $estructuraAGuardar = $estructuraSeleccionada;
        }

        // 2. Sanitizar y estandarizar la estructura (slashes al principio y al final)
        $estructuraAGuardar = '/' . trim($estructuraAGuardar, '/') . '/';

        try {
            // 3. Guardar la opción en la base de datos para consulta futura.
            $this->opcionService->guardarOpcion('permalink_structure', $estructuraAGuardar);

            // 4. Convertir la estructura legible a un patrón de ruta de Webman.
            $patronRuta = $this->convertirEstructuraARuta($estructuraAGuardar);
            if (empty($patronRuta)) {
                throw new \Exception('La estructura de enlace permanente proporcionada no es válida.');
            }

            // 5. Generar el contenido del nuevo archivo de configuración de rutas.
            $contenidoArchivo = "<?php\n";
            $contenidoArchivo .= "/**\n * Archivo de rutas de enlaces permanentes.\n * Este archivo es generado y sobreescrito automáticamente por los Ajustes de Enlaces Permanentes.\n * NO MODIFICAR MANUALMENTE.\n */\n\n";
            $contenidoArchivo .= "use Webman\\Route;\n";
            $contenidoArchivo .= "use App\\controller\\PaginaPublicaController;\n\n";
            $contenidoArchivo .= "// Estructura actual: " . htmlspecialchars($estructuraAGuardar) . "\n";
            $contenidoArchivo .= "Route::get('{$patronRuta}', [PaginaPublicaController::class, 'mostrar']);\n";

            // 6. Escribir el archivo. Esto activará el monitor de archivos para recargar el servidor.
            $rutaConfig = config_path('permalinks.inc.php');
            if (file_put_contents($rutaConfig, $contenidoArchivo) === false) {
                throw new \Exception("No se pudo escribir en el archivo de configuración de enlaces permanentes: {$rutaConfig}");
            }

            $request->session()->set('mensaje_exito', 'Ajustes de enlaces permanentes guardados. El servidor se recargará para aplicar los cambios.');
        } catch (\Throwable $e) {
            $request->session()->set('error', 'Error al guardar los ajustes: ' . $e->getMessage());
            \support\Log::error('Error al guardar enlaces permanentes: ' . $e->getMessage());
        }

        // 7. Redirigir de vuelta a la página de ajustes.
        return redirect('/panel/ajustes/enlaces-permanentes');
    }

    /**
     * Convierte una estructura de permalink con tags a un patrón de ruta para Webman.
     *
     * @param string $estructura La estructura con tags (ej: /%año%/%slug%/)
     * @return string El patrón de ruta (ej: /{año:\d{4}}/{slug:[a-zA-Z0-9\-_]+})
     */
    private function convertirEstructuraARuta(string $estructura): string
    {
        $reemplazos = [
            '/%año%/' => '/{año:\d{4}}',
            '/%mes%/' => '/{mes:\d{2}}',
            '/%dia%/' => '/{dia:\d{2}}',
            '/%slug%/' => '/{slug:[a-zA-Z0-9\-_]+}',
            '/%id%/'   => '/{id:\d+}'
        ];

        // Se usa str_replace para reemplazar los tags por su equivalente en regex.
        $patron = str_replace(array_keys($reemplazos), array_values($reemplazos), $estructura);

        // Limpiamos slashes duplicados y quitamos el slash del final para Webman.
        $patron = preg_replace('/\/+/', '/', $patron);
        $patron = rtrim($patron, '/');

        // Si el patrón queda vacío (ej: solo era '/'), lo devolvemos para evitar errores.
        return $patron === '' ? '/' : $patron;
    }
}
