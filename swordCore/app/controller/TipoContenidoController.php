<?php

namespace App\controller;

use App\model\Pagina;
use App\service\TipoContenidoService;
use support\Request;
use support\Response;

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
        $entradas = Pagina::where('tipocontenido', $slug)->orderBy('id', 'desc')->get();

        // Las vistas se unificarán en el siguiente paso.
        return view('admin/tipoContenido/index', [
            'entradas' => $entradas,
            'config' => $config,
            'slug' => $slug,
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

        $pagina = new Pagina;
        $pagina->titulo = $request->post('titulo');
        $pagina->contenido = $request->post('contenido', '');
        $pagina->slug = $this->generarSlug($request->post('titulo'));
        $pagina->tipocontenido = $slug; // <- Clave: Se asigna el tipo de contenido.
        $pagina->save();

        return redirect('/panel/' . $slug);
    }

    /**
     * Muestra el formulario para editar una entrada existente.
     */
    public function edit(Request $request, string $slug, int $id): Response
    {
        $config = $this->getConfigOr404($slug);
        $entrada = Pagina::where('id', $id)->where('tipocontenido', $slug)->firstOrFail();

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
        $pagina = Pagina::where('id', $id)->where('tipocontenido', $slug)->firstOrFail();

        $pagina->titulo = $request->post('titulo');
        $pagina->contenido = $request->post('contenido', '');
        $pagina->slug = $this->generarSlug($request->post('titulo'), $id);
        $pagina->save();

        return redirect('/panel/' . $slug);
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
     * Genera un slug único para un título.
     * Nota: Esta lógica podría moverse a un servicio o trait en el futuro para reutilizarse.
     */
    private function generarSlug(string $titulo, int $id = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
        $query = Pagina::where('slug', $slug);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        if ($query->exists()) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                $newQuery = Pagina::where('slug', $newSlug);
                if ($id !== null) {
                    $newQuery->where('id', '!=', $id);
                }
                $i++;
            } while ($newQuery->exists());
            return $newSlug;
        }

        return $slug;
    }
}
