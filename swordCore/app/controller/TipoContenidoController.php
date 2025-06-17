<?php

namespace App\controller;

use App\model\Pagina;
use App\service\TipoContenidoService;
use support\Request;
use support\Response;
use Illuminate\Support\Str;

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
            \Illuminate\Database\Capsule\Manager::transaction(function () use ($request, $slug) {
                // 1. Crear la entrada principal
                $pagina = new Pagina;
                $pagina->titulo = $request->post('titulo');
                $pagina->contenido = $request->post('contenido', '');
                $pagina->slug = $this->generarSlug($request->post('titulo'));
                $pagina->tipocontenido = $slug;
                $pagina->idautor = idUsuarioActual();
                $pagina->estado = $request->post('estado', 'borrador');
                $pagina->save();

                // 2. Procesar y guardar los metadatos
                $metadatosFormulario = $request->post('meta', []);
                if (is_array($metadatosFormulario)) {
                    foreach ($metadatosFormulario as $meta) {
                        if (isset($meta['clave']) && trim($meta['clave']) !== '' && !is_null($meta['valor'])) {
                            // Usamos el método del trait para crear/actualizar el meta
                            $pagina->guardarMeta(trim($meta['clave']), $meta['valor']);
                        }
                    }
                }
            });

            // CORRECCIÓN: Usar set() en lugar de flash()
            session()->set('success', 'Entrada creada con éxito.');
            return redirect('/panel/' . $slug);
        } catch (\Throwable $e) {
            // CORRECCIÓN: Usar set() en lugar de flash()
            session()->set('error', 'Error al crear la entrada: ' . $e->getMessage());
            // Guardamos el input para repoblar el formulario
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

        // Precargamos la relación 'metas' para que estén disponibles en la vista.
        $entrada = Pagina::with('metas')
            ->where('id', $id)
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
            \Illuminate\Database\Capsule\Manager::transaction(function () use ($request, $slug, $id) {
                // 1. Obtener y actualizar la entrada principal
                $pagina = Pagina::where('id', $id)->where('tipocontenido', $slug)->firstOrFail();

                $pagina->titulo = $request->post('titulo');
                $pagina->contenido = $request->post('contenido', '');
                
                // Usa el slug del formulario si se proporciona, si no, usa el título
                $baseParaSlug = $request->post('slug', $request->post('titulo'));
                $pagina->slug = $this->generarSlug($baseParaSlug, $id);

                $pagina->estado = $request->post('estado', 'borrador');
                $pagina->save();

                // 2. Borrar metadatos antiguos para sincronizar
                $pagina->metas()->delete();

                // 3. Procesar y guardar los nuevos metadatos
                $metadatosFormulario = $request->post('meta', []);
                $nuevosMetadatosParaInsertar = [];

                if (is_array($metadatosFormulario)) {
                    foreach ($metadatosFormulario as $meta) {
                        if (isset($meta['clave']) && trim($meta['clave']) !== '' && !is_null($meta['valor'])) {
                            $nuevosMetadatosParaInsertar[] = [
                                'pagina_id'  => $pagina->id,
                                'meta_key'   => trim($meta['clave']),
                                'meta_value' => $meta['valor'],
                            ];
                        }
                    }
                }

                if (!empty($nuevosMetadatosParaInsertar)) {
                    \App\model\PaginaMeta::insert($nuevosMetadatosParaInsertar);
                }
            });

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