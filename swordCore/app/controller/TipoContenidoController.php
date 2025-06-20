<?php

namespace App\controller;

use App\model\Pagina;
use App\service\TipoContenidoService;
use support\Request;
use support\Response;
use Illuminate\Support\Str;
use App\service\OpcionService;
use App\service\TemaService;

/**
 * Controlador genérico para gestionar las operaciones CRUD de los tipos de contenido.
 */
class TipoContenidoController
{
    /**
     * Muestra la lista de entradas para un tipo de contenido específico.
     */
    public function index(Request $request, string $slug): Response
    {
        $config = $this->getConfigOr404($slug);

        // Implementar paginación
        $porPagina = 10;
        $paginaActual = (int)$request->input('page', 1);
        $totalItems = Pagina::where('tipocontenido', $slug)->count();
        $totalPaginas = (int)ceil($totalItems / $porPagina);

        if ($paginaActual > $totalPaginas && $totalItems > 0) {
            $paginaActual = $totalPaginas;
        }
        if ($paginaActual < 1) {
            $paginaActual = 1;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $entradas = Pagina::where('tipocontenido', $slug)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($porPagina)
            ->get();

        // Extraer mensajes "flasheados" de la sesión
        $successMessage = $request->session()->pull('success');
        $errorMessage = $request->session()->pull('error');

        // Las vistas se unificarán en el siguiente paso.
        return view('admin/tipoContenido/index', [
            'entradas' => $entradas,
            'config' => $config,
            'slug' => $slug,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva entrada.
     */
    public function create(Request $request, string $slug): Response
    {
        $config = $this->getConfigOr404($slug);

        return view('admin/tipoContenido/create', [
            'config' => $config,
            'slug' => $slug,
        ]);
    }

    /**
     * Almacena una nueva entrada en la base de datos.
     */
    public function store(Request $request, string $slug): Response
    {
        $this->getConfigOr404($slug);

        try {
            // 1. Construir el array de metadatos desde el formulario
            $metadata = [];
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $metadata[trim($meta['clave'])] = $meta['valor'] ?? '';
                    }
                }
            }

            // === INICIO: LÓGICA IMAGEN DESTACADA ===
            $idImagenDestacada = $request->post('_imagen_destacada_id');
            if (!empty($idImagenDestacada) && is_numeric($idImagenDestacada)) {
                $metadata['_imagen_destacada_id'] = (int)$idImagenDestacada;
            }
            // === FIN: LÓGICA IMAGEN DESTACADA ===

            // 2. Preparar todos los datos para la creación
            $datosParaCrear = [
                'titulo'        => $request->post('titulo'),
                'contenido'     => $request->post('contenido', ''),
                'slug'          => $this->generarSlug($request->post('titulo')),
                'tipocontenido' => $slug,
                'idautor'       => idUsuarioActual(),
                'estado'        => $request->post('estado', 'borrador'),
                'metadata'      => $metadata, // Incluir el array de metadatos
            ];

            // 3. Crear la entrada en una sola operación
            Pagina::create($datosParaCrear);

            session()->set('success', 'Entrada creada con éxito.');
            return redirect('/panel/' . $slug);
        } catch (\Throwable $e) {
            session()->set('error', 'Error al crear la entrada: ' . $e->getMessage());
            session()->set('_old_input', $request->post());
            return redirect('/panel/' . $slug . '/crear');
        }
    }
    /**
     * Muestra el formulario para editar una entrada existente.
     */

    public function edit(Request $request, string $slug, int $id): Response
    {
        $config = $this->getConfigOr404($slug);

        // La relación 'metas' ya no existe. Los metadatos están en la columna 'metadata'.
        $entrada = Pagina::where('id', $id)
            ->where('tipocontenido', $slug)
            ->firstOrFail();

        return view('admin/tipoContenido/edit', [
            'entrada' => $entrada,
            'config' => $config,
            'slug' => $slug,
        ]);
    }

    /**
     * Actualiza una entrada existente en la base de datos.
     */
    public function update(Request $request, string $slug, int $id): Response
    {
        $this->getConfigOr404($slug);

        try {
            $pagina = Pagina::where('id', $id)->where('tipocontenido', $slug)->firstOrFail();

            // Obtenemos los metadatos existentes para no perderlos
            $metadata = $pagina->metadata ?? [];

            // Procesamos los metadatos personalizados del formulario
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                $nuevosMetadatos = [];
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $clave = trim($meta['clave']);
                        $nuevosMetadatos[$clave] = $meta['valor'] ?? '';
                    }
                }
                // Sobrescribimos solo los metas personalizados, manteniendo los internos
                foreach ($metadata as $key => $value) {
                    if (str_starts_with($key, '_')) {
                        $nuevosMetadatos[$key] = $value;
                    }
                }
                $metadata = $nuevosMetadatos;
            }

            // === INICIO: LÓGICA IMAGEN DESTACADA ===
            $idImagenDestacada = $request->post('_imagen_destacada_id');
            if (!empty($idImagenDestacada) && is_numeric($idImagenDestacada)) {
                $metadata['_imagen_destacada_id'] = (int)$idImagenDestacada;
            } else {
                // Si el input llega vacío, se elimina la meta
                unset($metadata['_imagen_destacada_id']);
            }
            // === FIN: LÓGICA IMAGEN DESTACADA ===

            // Asignar datos principales
            $pagina->titulo = $request->post('titulo');
            $pagina->contenido = $request->post('contenido', '');
            $pagina->estado = $request->post('estado', 'borrador');

            // Generar y asignar slug único
            $baseParaSlug = $request->post('slug', $request->post('titulo'));
            $pagina->slug = $this->generarSlug($baseParaSlug, $id);

            // Asignar el array completo de metadatos a la propiedad del modelo
            $pagina->metadata = $metadata;

            // Guardar todos los cambios en una sola operación
            $pagina->save();

            session()->set('success', 'Entrada actualizada con éxito.');
            return redirect('/panel/' . $slug);
        } catch (\Throwable $e) {
            session()->set('error', 'Error al actualizar la entrada: ' . $e->getMessage());
            session()->set('_old_input', $request->post());
            return redirect('/panel/' . $slug . '/editar/' . $id);
        }
    }


    /**
     * Elimina una entrada, asegurándose de que coincida con el tipo de contenido.
     */
    public function destroy(Request $request, string $slug, int $id): Response
    {
        $this->getConfigOr404($slug);
        $pagina = Pagina::where('id', $id)->where('tipocontenido', $slug)->firstOrFail();
        $pagina->delete();

        return redirect('/panel/' . $slug);
    }

    /**
     * Muestra la página de ajustes para un tipo de contenido.
     */
    public function mostrarAjustes(Request $request, string $slug): Response
    {
        $config = $this->getConfigOr404($slug);
        $temaService = new TemaService();
        $opcionService = new OpcionService();

        // 1. Obtener las plantillas de página disponibles del tema.
        $plantillasDisponibles = $temaService->obtenerPlantillasDePagina();

        // 2. Obtener los ajustes previamente guardados para este tipo de contenido.
        $ajustesGuardados = $opcionService->obtenerOpcion("ajustes_cpt_{$slug}", [
            'plantilla_single' => '',
            // 'roles_permitidos' => ['admin'], // Para futura implementación
        ]);

        $mensajeExito = $request->session()->pull('success');

        return view('admin/tipoContenido/ajustes', [
            'config' => $config,
            'slug' => $slug,
            'tituloPagina' => 'Ajustes de ' . ($config['labels']['name'] ?? ucfirst($slug)),
            'plantillasDisponibles' => $plantillasDisponibles,
            'ajustesGuardados' => $ajustesGuardados,
            'mensajeExito' => $mensajeExito
        ]);
    }

    /**
     * Guarda los ajustes para un tipo de contenido.
     */
    public function guardarAjustes(Request $request, string $slug): Response
    {
        $this->getConfigOr404($slug);
        $opcionService = new OpcionService();

        // Obtener los valores del formulario.
        $plantilla = $request->post('plantilla_single', '');

        $ajustesAGuardar = [
            'plantilla_single' => $plantilla,
            // 'roles_permitidos' => $request->post('roles', []), // Para futura implementación
        ];

        // Guardar la opción en la base de datos.
        $opcionService->guardarOpcion("ajustes_cpt_{$slug}", $ajustesAGuardar);

        $request->session()->set('success', 'Ajustes guardados correctamente.');
        return redirect('/panel/' . $slug . '/ajustes');
    }

    /**
     * Obtiene la configuración del tipo de contenido o aborta con un error 404 si no existe.
     */
    private function getConfigOr404(string $slug): array
    {
        $config = TipoContenidoService::getInstancia()->obtener($slug);
        if (!$config) {
            abort(404, 'Tipo de contenido no encontrado.');
        }
        return $config;
    }

    /**
     * Genera un slug único para un título o texto base.
     */
    private function generarSlug(string $textoBase, ?int $idExcluir = null): string
    {
        $slug = Str::slug($textoBase);
        $slugBase = $slug;
        $contador = 1;

        while (true) {
            $query = Pagina::where('slug', $slug);

            if ($idExcluir !== null) {
                $query->where('id', '!=', $idExcluir);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = "{$slugBase}-{$contador}";
            $contador++;
        }

        return $slug;
    }
}