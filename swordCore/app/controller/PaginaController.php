<?php

namespace App\controller;

use App\model\Pagina;
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
        // --- Lógica de Paginación Nativa ---
        $porPagina = 10;
        $paginaActual = (int)$request->input('page', 1);

        // CORRECCIÓN: Contar solo las entradas cuyo tipo de contenido es 'pagina'.
        $totalItems = Pagina::where('tipocontenido', 'pagina')->count();
        $totalPaginas = (int)ceil($totalItems / $porPagina);

        if ($paginaActual > $totalPaginas && $totalItems > 0) {
            $paginaActual = $totalPaginas;
        }
        if ($paginaActual < 1) {
            $paginaActual = 1;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        // CORRECCIÓN: Obtener solo las entradas cuyo tipo de contenido es 'pagina'.
        // Se utiliza with('autor') para cargar la relación y evitar consultas N+1.
        // Se ordena por fecha de creación descendente para mostrar las más nuevas primero.
        $paginas = Pagina::with('autor')
            ->where('tipocontenido', 'pagina') // <-- AÑADIDO
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($porPagina)
            ->get();
        // --- Fin de la Lógica de Paginación ---

        // Renderiza la vista, pasando todos los datos necesarios.
        return view('admin/paginas/index', [
            'paginas' => $paginas,
            'tituloPagina' => 'Gestión de Páginas', // Título que la vista espera.
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
        // Validación
        $data = $request->post();
        if (empty($data['titulo'])) {
            session()->flash('error', 'El campo Título es obligatorio.');
            session()->flash('_old_input', $request->post());
            return redirect('/panel/paginas/create');
        }

        $pagina = new Pagina();
        // Asignación de campos básicos
        $pagina->titulo = $request->post('titulo');
        $pagina->subtitulo = $request->post('subtitulo');
        $pagina->contenido = $request->post('contenido');
        $pagina->estado = $request->post('estado');

        // Corrección 1: Asignar el ID del autor correctamente
        $pagina->idautor = idUsuarioActual();

        // Corrección 2: Generar el slug a partir del título
        $slugBase = \Illuminate\Support\Str::slug($request->post('titulo'));
        $slug = $slugBase;
        $contador = 1;
        // Bucle para asegurar slug único
        while (Pagina::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $contador++;
        }
        $pagina->slug = $slug;

        $pagina->save();

        // Mejora: Procesar metadatos que antes se ignoraban
        $metadatos = $request->post('meta', []);
        if (is_array($metadatos)) {
            foreach ($metadatos as $clave => $valor) {
                // Guardar meta solo si tiene un valor
                if (trim($valor) !== '') {
                    $pagina->guardarMeta($clave, $valor);
                }
            }
        }

        // Corrección 3: Usar flash en la sesión para el mensaje de éxito
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