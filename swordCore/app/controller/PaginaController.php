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
        
        return view('admin/paginas/index', [
            'paginas' => $paginas,
            'tituloPagina' => 'Gestión de Páginas',
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
        ]);
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
    public function store(Request $request)
    {
        // Esta lógica aún necesita ser actualizada para manejar los metadatos del nuevo componente.
        $data = $request->post();
        if (empty($data['titulo'])) {
            session()->flash('error', 'El campo Título es obligatorio.');
            session()->flash('_old_input', $request->post());
            return redirect('/panel/paginas/create');
        }

        $pagina = new Pagina();
        $pagina->titulo = $request->post('titulo');
        $pagina->subtitulo = $request->post('subtitulo');
        $pagina->contenido = $request->post('contenido');
        $pagina->estado = $request->post('estado');
        $pagina->idautor = idUsuarioActual();

        $slugBase = \Illuminate\Support\Str::slug($request->post('titulo'));
        $slug = $slugBase;
        $contador = 1;
        while (Pagina::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $contador++;
        }
        $pagina->slug = $slug;

        $pagina->save();

        $metadatos = $request->post('meta', []);
        if (is_array($metadatos)) {
            foreach ($metadatos as $clave => $valor) {
                if (trim($valor) !== '') {
                    $pagina->guardarMeta($clave, $valor);
                }
            }
        }

        session()->flash('success', 'Página creada con éxito.');
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
            session()->flash('error', 'La página que intentas editar no existe.');
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
                
                $pagina = $this->paginaService->obtenerPaginaPorId($id);

                $datosPrincipales = $request->except(['meta', '_csrf']);
                $this->paginaService->actualizarPagina($pagina, $datosPrincipales);

                // CORRECCIÓN: Usar el nombre de la relación correcto 'metas'.
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
                                    'clave'      => $clave,
                                    'valor'      => $valor,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }

                if (!empty($nuevosMetadatosParaInsertar)) {
                    PaginaMeta::insert($nuevosMetadatosParaInsertar);
                }
            });

            session()->flash('success', 'Página actualizada con éxito.');
            return redirect('/panel/paginas');

        } catch (NotFoundException $e) {
            session()->flash('error', 'La página que intentas actualizar no existe.');
            return redirect('/panel/paginas');
        } catch (Throwable $e) {
            session()->flash('error', 'Ocurrió un error al actualizar la página: ' . $e->getMessage());
            return redirect('/panel/paginas/edit/' . $id)->withInput($request->all());
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
            session()->flash('success', 'Página eliminada con éxito.');
            return redirect('/panel/paginas');
        } catch (Throwable $e) {
            session()->flash('error', $e->getMessage());
            return redirect('/panel/paginas');
        }
    }
}