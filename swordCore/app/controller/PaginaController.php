<?php

namespace App\controller;

use App\model\Pagina;
use App\model\PaginaMeta;
use App\service\PaginaService;
use support\Request;
use support\Response;
use Throwable;
use Webman\Exception\NotFoundException;
use App\service\OpcionService;

/**
 * Class PaginaController
 * @package App\controller
 */
class PaginaController
{
    /**
     * @var PaginaService
     * @var OpcionService
     * @var TemaService
     */
    private PaginaService $paginaService;
    private OpcionService $opcionService;
    private \App\service\TemaService $temaService;

    /**
     * Constructor
     * @param PaginaService $paginaService
     * @param OpcionService $opcionService
     * @param \App\service\TemaService $temaService
     */
    public function __construct(PaginaService $paginaService, OpcionService $opcionService, \App\service\TemaService $temaService)
    {
        $this->paginaService = $paginaService;
        $this->opcionService = $opcionService;
        $this->temaService = $temaService;
    }

    /**
     * Muestra la lista de páginas.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $porPagina = 10;
        $paginaActual = (int)$request->input('page', 1);
        $totalItems = Pagina::where('tipocontenido', 'pagina')->count();
        $totalPaginas = (int)ceil($totalItems / $porPagina);

        if ($paginaActual > $totalPaginas && $totalItems > 0) {
            $paginaActual = $totalPaginas;
        }
        if ($paginaActual < 1) {
            $paginaActual = 1;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        // Obtener el slug de la página de inicio para la ordenación y la vista.
        $slugPaginaInicio = $this->opcionService->obtenerOpcion('pagina_de_inicio_slug');

        $query = Pagina::with('autor')->where('tipocontenido', 'pagina');

        // Aplicar ordenación personalizada: página de inicio primero.
        if ($slugPaginaInicio) {
            $query->orderByRaw("CASE WHEN slug = ? THEN 0 ELSE 1 END ASC", [$slugPaginaInicio]);
        }
        $query->orderBy('created_at', 'desc');

        $paginas = $query->offset($offset)->limit($porPagina)->get();

        $successMessage = $request->session()->pull('success');
        $errorMessage = $request->session()->pull('error');

        return view('admin/paginas/index', [
            'paginas' => $paginas,
            'tituloPagina' => 'Gestión de Páginas',
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'slugPaginaInicio' => $slugPaginaInicio, // Pasar el slug a la vista
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva página.
     * @param Request $request
     * @return Response
     */

    public function create(Request $request): Response
    {
        // Limpiamos los datos de 'old input' para asegurar que el formulario de creación aparezca siempre vacío
        // y no herede datos de envíos de formularios anteriores que hayan fallado.
        $request->session()->forget('_old_input');

        // Extraer mensajes de error de la sesión para pasarlos a la vista.
        $errorMessage = $request->session()->pull('error');

        // Obtenemos las plantillas de página disponibles desde el servicio de temas.
        $plantillasDisponibles = $this->temaService->obtenerPlantillasDePagina();

        return view('admin/paginas/create', [
            'tituloPagina' => 'Crear Nueva Página',
            'errorMessage' => $errorMessage,
            'plantillasDisponibles' => $plantillasDisponibles // Pasamos las plantillas a la vista.
        ]);
    }
    /**
     * Almacena una nueva página en la base de datos.
     * @param Request $request
     * @return Response
     */

    public function store(Request $request)
    {
        $data = $request->post();

        if (empty($data['titulo'])) {
            $request->session()->set('error', 'El campo Título es obligatorio.');
            $request->session()->set('_old_input', $request->post());
            return redirect('/panel/paginas/create');
        }

        try {
            // Construimos el array de metadatos
            $metadata = [];
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $metadata[trim($meta['clave'])] = $meta['valor'] ?? '';
                    }
                }
            }
            // ... (lógica de plantilla existente)
            $plantillaSeleccionada = $request->post('_plantilla_pagina');
            if (!empty($plantillaSeleccionada)) {
                $metadata['_plantilla_pagina'] = $plantillaSeleccionada;
            }

            // === INICIO: LÓGICA IMAGEN DESTACADA ===
            $idImagenDestacada = $request->post('_imagen_destacada_id');
            if (!empty($idImagenDestacada) && is_numeric($idImagenDestacada)) {
                $metadata['_imagen_destacada_id'] = (int)$idImagenDestacada;
            }
            // === FIN: LÓGICA IMAGEN DESTACADA ===

            $datosPrincipales = $request->except(['meta', '_csrf', '_plantilla_pagina', '_imagen_destacada_id']);
            $datosPrincipales['metadata'] = $metadata;

            $this->paginaService->crearPagina($datosPrincipales);

            $request->session()->set('success', 'Página creada con éxito.');
            return redirect('/panel/paginas');
        } catch (\Exception $e) {
            $request->session()->set('error', 'Error al crear la página: ' . $e->getMessage());
            $request->session()->set('_old_input', $request->post());
            return redirect('/panel/paginas/create');
        }
    }
    /**
     * Muestra el formulario para editar una página existente.
     * @param Request $request
     * @param $id
     * @return Response
     */

    public function edit(Request $request, $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId((int)$id);
            $errorMessage = $request->session()->pull('error');

            // Obtenemos las plantillas de página disponibles desde el servicio de temas.
            $plantillasDisponibles = $this->temaService->obtenerPlantillasDePagina();

            return view('admin/paginas/edit', [
                'pagina' => $pagina,
                'tituloPagina' => 'Editar Página',
                'errorMessage' => $errorMessage,
                'plantillasDisponibles' => $plantillasDisponibles // Pasamos las plantillas a la vista.
            ]);
        } catch (NotFoundException $e) {
            $request->session()->set('error', 'La página que intentas editar no existe.');
            return redirect('/panel/paginas');
        }
    }

    /**
     * Actualiza una página existente en la base de datos.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        try {
            $pagina = $this->paginaService->obtenerPaginaPorId((int)$id);

            // Obtenemos los metadatos existentes para no perderlos
            $metadata = $pagina->metadata ?? [];

            // Procesamos los metadatos del formulario (gestor-metadatos.php)
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                // Sincronizamos los metas personalizados, borrando los que ya no están
                $clavesEnviadas = [];
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $clave = trim($meta['clave']);
                        $metadata[$clave] = $meta['valor'] ?? '';
                        $clavesEnviadas[] = $clave;
                    }
                }
                // Lógica para eliminar metas que se borraron desde la UI (opcional pero recomendado)
                // Esto requiere un reajuste en cómo se manejan los metas, por simplicidad lo omitimos aquí.
                // La forma más simple es que el gestor de metas siempre envíe todas las claves.
            }

            $plantillaSeleccionada = $request->post('_plantilla_pagina');
            if (!empty($plantillaSeleccionada)) {
                $metadata['_plantilla_pagina'] = $plantillaSeleccionada;
            } else {
                unset($metadata['_plantilla_pagina']); // Quitar si se des-selecciona
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

            $datosPrincipales = $request->except(['meta', '_csrf', '_plantilla_pagina', '_imagen_destacada_id']);
            $datosPrincipales['metadata'] = $metadata;

            $this->paginaService->actualizarPagina($pagina, $datosPrincipales);

            $request->session()->set('success', 'Página actualizada con éxito.');
            return redirect('/panel/paginas');
        } catch (NotFoundException $e) {
            $request->session()->set('error', 'La página que intentas actualizar no existe.');
            return redirect('/panel/paginas');
        } catch (Throwable $e) {
            $request->session()->set('error', 'Ocurrió un error al actualizar la página: ' . $e->getMessage());
            $request->session()->set('_old_input', $request->all());
            return redirect('/panel/paginas/edit/' . $id);
        }
    }

    /**
     * Elimina una página.
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->paginaService->eliminarPagina((int)$id);
            $request->session()->set('success', 'Página eliminada con éxito.');
        } catch (NotFoundException $e) {
            $request->session()->set('error', 'La página que intentas eliminar no existe.');
        } catch (\Exception $e) {
            $request->session()->set('error', 'Ocurrió un error al eliminar la página: ' . $e->getMessage());
        }

        return redirect('/panel/paginas');
    }
}
