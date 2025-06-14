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
            // Se "flashea" el mensaje de éxito a la sesión ANTES de redirigir.
            session()->flash('success', 'Página creada con éxito.');
            return redirect('/panel/paginas'); // O /admin/paginas según tu ruta
        } catch (Throwable $e) {
            // En caso de error, flasheamos el mensaje y los datos del formulario.
            session()->flash('error', $e->getMessage());
            // Esto es CLAVE para que la función old() funcione.
            session()->flash('_old_input', $request->post());
            return redirect('/panel/paginas/create'); // O /admin/paginas/create
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
            // Se flashea el mensaje de error a la sesión.
            session()->flash('error', 'La página que intentas editar no existe.');
            return redirect('/panel/paginas'); // O /admin/paginas
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
            // Se flashea el mensaje de éxito.
            session()->flash('success', 'Página actualizada con éxito.');
            return redirect('/panel/paginas'); // O /admin/paginas
        } catch (Throwable $e) {
            // En caso de error, flasheamos el mensaje y los datos del formulario.
            session()->flash('error', $e->getMessage());
            // Esto es CLAVE para que la función old() funcione.
            session()->flash('_old_input', $request->post());
            return redirect('/panel/paginas/edit/' . $id); // O /admin/paginas/edit/
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