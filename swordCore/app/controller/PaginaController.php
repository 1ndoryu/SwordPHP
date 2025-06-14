<?php

namespace App\controller;

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
        $paginas = $this->paginaService->obtenerPaginasPaginadas();
        return view('admin/paginas/index', ['paginas' => $paginas]);
    }

    /**
     * Muestra el formulario para crear una nueva página.
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return view('admin/paginas/create');
    }

    /**
     * Almacena una nueva página en la base de datos.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // 1. Crear la página con los datos principales
        // Excluimos 'meta' para pasarlo al servicio de forma segura.
        $datosPrincipales = $request->except(['meta', '_csrf']);
        $pagina = $this->paginaService->crearPagina($datosPrincipales);

        // 2. Si la página se creó correctamente, procesamos los metadatos.
        if ($pagina) {
            $metadatos = $request->post('meta', []);
            if (is_array($metadatos)) {
                foreach ($metadatos as $clave => $valor) {
                    // Solo guardamos el metadato si el usuario introdujo un valor.
                    if (trim($valor) !== '') {
                        $pagina->guardarMeta($clave, $valor);
                    }
                }
            }
        }

        // Opcional: añadir un mensaje de éxito a la sesión.
        return redirect('/panel/paginas');
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
            return view('admin/paginas/edit', ['pagina' => $pagina]);
        } catch (NotFoundException $e) {
            // Se flashea el mensaje de error a la sesión.
            session()->flash('error', 'La página que intentas editar no existe.');
            return redirect('/panel/paginas'); // O /admin/paginas
        }
    }

    /**
     * Actualiza una página existente en la base de datos.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        $pagina = $this->paginaService->obtenerPaginaPorId($id);
        if (!$pagina) {
            // En un futuro, se podría redirigir con un mensaje de error.
            return response('Página no encontrada', 404);
        }

        // 1. Actualizar los datos principales de la página (columnas de la tabla 'paginas')
        // Se excluye el campo 'meta' para evitar errores de asignación masiva.
        $datosPrincipales = $request->except(['meta', '_csrf']);
        $this->paginaService->actualizarPagina($pagina, $datosPrincipales);

        // 2. Procesar y guardar los metadatos
        $metadatos = $request->post('meta', []);
        if (is_array($metadatos)) {
            foreach ($metadatos as $clave => $valor) {
                // Si el valor enviado está vacío, eliminamos el metadato si existe.
                // De lo contrario, lo guardamos (el método se encarga de crear o actualizar).
                if (trim($valor) === '') {
                    $pagina->eliminarMeta($clave);
                } else {
                    $pagina->guardarMeta($clave, $valor);
                }
            }
        }

        // 3. Redirigir de vuelta al listado
        // Opcional: añadir un mensaje de éxito a la sesión.
        return redirect('/panel/paginas');
    }

    /**
     * Elimina una página.
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function destroy(Request $request, $id): Response
    {
        try {
            $this->paginaService->eliminarPagina((int)$id);
            // Se flashea el mensaje de éxito.
            session()->flash('success', 'Página eliminada con éxito.');
            return redirect('/panel/paginas'); // O /admin/paginas
        } catch (Throwable $e) {
            // Se flashea el mensaje de error.
            session()->flash('error', $e->getMessage());
            return redirect('/panel/paginas'); // O /admin/paginas
        }
    }
}
