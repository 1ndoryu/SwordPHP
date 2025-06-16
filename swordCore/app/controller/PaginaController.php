<?php

namespace App\controller;

use App\model\Pagina;
use App\model\PaginaMeta;
use App\service\PaginaService;
use support\Request;
use support\Response;
use Throwable;
use Webman\Exception\NotFoundException;


/**
 * Class PaginaController
 * @package App\controller
 */
class PaginaController
{
    /**
     * @var PaginaService
     */
    private PaginaService $paginaService;

    /**
     * Constructor
     * @param PaginaService $paginaService
     */
    public function __construct(PaginaService $paginaService)
    {
        $this->paginaService = $paginaService;
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

        $paginas = Pagina::with('autor')
            ->where('tipocontenido', 'pagina')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($porPagina)
            ->get();

        $successMessage = $request->session()->pull('success');
        $errorMessage = $request->session()->pull('error');
        
        return view('admin/paginas/index', [
            'paginas' => $paginas,
            'tituloPagina' => 'Gestión de Páginas',
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva página.
     * @param Request $request
     * @return Response
     */

    public function create(Request $request): Response
    {
        // REFACTOR: Extraer mensajes de error de la sesión para pasarlos a la vista.
        $errorMessage = $request->session()->pull('error');

        return view('admin/paginas/create', [
            'tituloPagina' => 'Crear Nueva Página',
            'errorMessage' => $errorMessage
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

        // Validación básica
        if (empty($data['titulo'])) {
            $request->session()->set('error', 'El campo Título es obligatorio.');
            $request->session()->set('_old_input', $request->post());
            return redirect('/panel/paginas/create');
        }

        try {
            \Illuminate\Database\Capsule\Manager::transaction(function () use ($request) {
                // 1. Crear la página principal
                $datosPrincipales = $request->except(['meta', '_csrf']);
                $pagina = $this->paginaService->crearPagina($datosPrincipales);

                // 2. Procesar y guardar los metadatos
                $metadatosFormulario = $request->post('meta', []);
                $nuevosMetadatosParaInsertar = [];

                if (is_array($metadatosFormulario)) {
                    foreach ($metadatosFormulario as $meta) {
                        if (isset($meta['clave']) && trim($meta['clave']) !== '' && strlen(trim($meta['clave'])) <= 255) {
                            $clave = trim($meta['clave']);
                            $valor = $meta['valor'] ?? '';

                            if ($valor !== '') {
                                $nuevosMetadatosParaInsertar[] = [
                                    'pagina_id'  => $pagina->id,
                                    'meta_key'   => $clave,
                                    'meta_value' => $valor,
                                ];
                            }
                        }
                    }
                }

                if (!empty($nuevosMetadatosParaInsertar)) {
                    PaginaMeta::insert($nuevosMetadatosParaInsertar);
                }
            });

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

            // REFACTOR: Extraer mensaje de error de la sesión.
            $errorMessage = $request->session()->pull('error');

            return view('admin/paginas/edit', [
                'pagina' => $pagina,
                'tituloPagina' => 'Editar Página',
                'errorMessage' => $errorMessage
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
            \Illuminate\Database\Capsule\Manager::transaction(function () use ($request, $id) {

                $pagina = $this->paginaService->obtenerPaginaPorId((int)$id);

                $datosPrincipales = $request->except(['meta', '_csrf']);
                $this->paginaService->actualizarPagina($pagina, $datosPrincipales);

                $pagina->metas()->delete();

                $metadatosFormulario = $request->post('meta', []);
                $nuevosMetadatosParaInsertar = [];

                if (is_array($metadatosFormulario)) {
                    foreach ($metadatosFormulario as $meta) {
                        if (isset($meta['clave']) && trim($meta['clave']) !== '' && strlen(trim($meta['clave'])) <= 255) {
                            $clave = trim($meta['clave']);
                            $valor = $meta['valor'] ?? '';

                            if ($valor !== '') {
                                $nuevosMetadatosParaInsertar[] = [
                                    'pagina_id'  => $pagina->id,
                                    'meta_key'   => $clave, // CORRECCIÓN: Nombre de columna correcto.
                                    'meta_value' => $valor, // CORRECCIÓN: Nombre de columna correcto.
                                ];
                            }
                        }
                    }
                }

                if (!empty($nuevosMetadatosParaInsertar)) {
                    PaginaMeta::insert($nuevosMetadatosParaInsertar);
                }
            });

            // CORRECCIÓN: Usar el método set() de la sesión a través del objeto Request.
            $request->session()->set('success', 'Página actualizada con éxito.');
            return redirect('/panel/paginas');
        } catch (NotFoundException $e) {
            // CORRECCIÓN: Usar el método set() de la sesión.
            $request->session()->set('error', 'La página que intentas actualizar no existe.');
            return redirect('/panel/paginas');
        } catch (Throwable $e) {
            // CORRECCIÓN: Usar set() para el mensaje de error y para guardar el "old input".
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
