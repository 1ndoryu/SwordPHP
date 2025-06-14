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
        try {
            $this->paginaService->crearPagina($request->post());
            return redirect('/admin/paginas')->with('success', 'Página creada con éxito.');
        } catch (Throwable $e) {
            return redirect('/admin/paginas/create')->with('error', $e->getMessage())->with('inputs', $request->post());
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
            return view('admin/paginas/edit', ['pagina' => $pagina]);
        } catch (NotFoundException $e) {
            return redirect('/admin/paginas')->with('error', 'La página que intentas editar no existe.');
        }
    }

    /**
     * Actualiza una página existente en la base de datos.
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        try {
            $this->paginaService->actualizarPagina((int)$id, $request->post());
            return redirect('/admin/paginas')->with('success', 'Página actualizada con éxito.');
        } catch (Throwable $e) {
            return redirect('/admin/paginas/edit/' . $id)->with('error', $e->getMessage())->with('inputs', $request->post());
        }
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
            return redirect('/admin/paginas')->with('success', 'Página eliminada con éxito.');
        } catch (Throwable $e) {
            return redirect('/admin/paginas')->with('error', $e->getMessage());
        }
    }
}
