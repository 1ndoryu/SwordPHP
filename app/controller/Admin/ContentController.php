<?php

namespace app\controller\Admin;

use support\Request;
use app\model\Content;

/**
 * Controlador para la gestion de contenidos en el panel admin.
 * Implementa CRUD completo de posts y paginas.
 */
class ContentController
{
    /**
     * Numero de elementos por pagina para paginacion.
     */
    private const ITEMS_PER_PAGE = 15;

    /**
     * Muestra el listado de contenidos con filtros y paginacion.
     */
    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $type = $request->get('type', '');
        $status = $request->get('status', '');
        $search = $request->get('search', '');

        $query = Content::with('user')->orderBy('created_at', 'desc');

        // Aplicar filtro por tipo
        if (!empty($type)) {
            $query->where('type', $type);
        }

        // Aplicar filtro por estado
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Aplicar busqueda por titulo
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("content_data->>'title' ILIKE ?", ["%{$search}%"])
                    ->orWhere('slug', 'ILIKE', "%{$search}%");
            });
        }

        $total = $query->count();
        $totalPages = ceil($total / self::ITEMS_PER_PAGE);
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        $contents = $query->skip($offset)->take(self::ITEMS_PER_PAGE)->get();

        // Obtener tipos unicos para el filtro
        $types = Content::select('type')->distinct()->pluck('type')->toArray();

        $viewData = [
            'contents' => $contents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'types' => $types,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'search' => $search
            ]
        ];

        $content = render_view('admin/pages/contents/index', $viewData);
        return render_view('admin/layouts/layout', [
            'title' => 'Contenidos',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]);
    }

    /**
     * Muestra el formulario de creacion de contenido.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'post');

        $content = render_view('admin/pages/contents/editor', [
            'mode' => 'create',
            'type' => $type,
            'content' => null
        ]);

        return render_view('admin/layouts/layout', [
            'title' => 'Nuevo Contenido',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]);
    }

    /**
     * Almacena un nuevo contenido.
     */
    public function store(Request $request)
    {
        $userId = $request->session()->get('admin_user_id');

        $title = $request->post('title', '');
        $contentBody = $request->post('content', '');
        $slug = $request->post('slug', '');
        $type = $request->post('type', 'post');
        $status = $request->post('status', 'draft');

        // Generar slug si esta vacio
        if (empty($slug)) {
            $slug = $this->generateSlug($title);
        }

        // Asegurar slug unico
        $slug = $this->ensureUniqueSlug($slug);

        // Construir content_data con title, content y metadatos
        $contentData = [
            'title' => $title,
            'content' => $contentBody
        ];

        // Procesar metadatos adicionales
        $metaKeys = $request->post('meta_keys', []);
        $metaValues = $request->post('meta_values', []);
        $metaIsJson = $request->post('meta_is_json', []);

        if (is_array($metaKeys)) {
            foreach ($metaKeys as $index => $key) {
                $key = trim($key);
                if (!empty($key) && !in_array($key, ['title', 'content'])) {
                    $value = $metaValues[$index] ?? '';
                    $isJson = ($metaIsJson[$index] ?? '0') === '1';

                    if ($isJson) {
                        $decoded = json_decode($value, true);
                        $contentData[$key] = $decoded !== null ? $decoded : $value;
                    } else {
                        $contentData[$key] = $value;
                    }
                }
            }
        }

        $newContent = Content::create([
            'slug' => $slug,
            'type' => $type,
            'status' => $status,
            'user_id' => $userId,
            'content_data' => $contentData
        ]);

        return redirect('/admin/contents/' . $newContent->id . '/edit?saved=1');
    }

    /**
     * Muestra el formulario de edicion de contenido.
     */
    public function edit(Request $request, int $id)
    {
        $contentItem = Content::find($id);

        if (!$contentItem) {
            return redirect('/admin/contents?error=not_found');
        }

        $saved = $request->get('saved', 0);

        $content = render_view('admin/pages/contents/editor', [
            'mode' => 'edit',
            'type' => $contentItem->type,
            'contentItem' => $contentItem,
            'saved' => $saved
        ]);

        return render_view('admin/layouts/layout', [
            'title' => 'Editar Contenido',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]);
    }

    /**
     * Actualiza un contenido existente.
     */
    public function update(Request $request, int $id)
    {
        $contentItem = Content::find($id);

        if (!$contentItem) {
            return redirect('/admin/contents?error=not_found');
        }

        $title = $request->post('title', '');
        $contentBody = $request->post('content', '');
        $slug = $request->post('slug', '');
        $status = $request->post('status', 'draft');

        // Generar slug si esta vacio
        if (empty($slug)) {
            $slug = $this->generateSlug($title);
        }

        // Asegurar slug unico (excluyendo el actual)
        if ($slug !== $contentItem->slug) {
            $slug = $this->ensureUniqueSlug($slug, $id);
        }

        // Construir content_data con title, content y metadatos
        $contentData = [
            'title' => $title,
            'content' => $contentBody
        ];

        // Procesar metadatos adicionales
        $metaKeys = $request->post('meta_keys', []);
        $metaValues = $request->post('meta_values', []);
        $metaIsJson = $request->post('meta_is_json', []);

        if (is_array($metaKeys)) {
            foreach ($metaKeys as $index => $key) {
                $key = trim($key);
                if (!empty($key) && !in_array($key, ['title', 'content'])) {
                    $value = $metaValues[$index] ?? '';
                    $isJson = ($metaIsJson[$index] ?? '0') === '1';

                    if ($isJson) {
                        $decoded = json_decode($value, true);
                        $contentData[$key] = $decoded !== null ? $decoded : $value;
                    } else {
                        $contentData[$key] = $value;
                    }
                }
            }
        }

        $contentItem->slug = $slug;
        $contentItem->status = $status;
        $contentItem->content_data = $contentData;
        $contentItem->save();

        return redirect('/admin/contents/' . $id . '/edit?saved=1');
    }

    /**
     * Envia un contenido a la papelera (soft delete).
     */
    public function destroy(Request $request, int $id)
    {
        $contentItem = Content::find($id);

        if (!$contentItem) {
            return json(['success' => false, 'message' => 'Contenido no encontrado']);
        }

        $contentItem->delete();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido enviado a la papelera']);
        }

        return redirect('/admin/contents?trashed=1');
    }

    /**
     * Muestra el listado de contenidos en la papelera.
     */
    public function trash(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');

        $query = Content::onlyTrashed()->with('user')->orderBy('deleted_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("content_data->>'title' ILIKE ?", ["%{$search}%"])
                    ->orWhere('slug', 'ILIKE', "%{$search}%");
            });
        }

        $total = $query->count();
        $totalPages = ceil($total / self::ITEMS_PER_PAGE);
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        $contents = $query->skip($offset)->take(self::ITEMS_PER_PAGE)->get();

        $viewData = [
            'contents' => $contents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filters' => [
                'search' => $search
            ]
        ];

        $content = render_view('admin/pages/contents/trash', $viewData);
        return render_view('admin/layouts/layout', [
            'title' => 'Papelera',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content
        ]);
    }

    /**
     * Restaura un contenido de la papelera.
     */
    public function restore(Request $request, int $id)
    {
        $contentItem = Content::onlyTrashed()->find($id);

        if (!$contentItem) {
            return json(['success' => false, 'message' => 'Contenido no encontrado en papelera']);
        }

        $contentItem->restore();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido restaurado correctamente']);
        }

        return redirect('/admin/contents/trash?restored=1');
    }

    /**
     * Elimina permanentemente un contenido de la papelera.
     */
    public function forceDestroy(Request $request, int $id)
    {
        $contentItem = Content::onlyTrashed()->find($id);

        if (!$contentItem) {
            return json(['success' => false, 'message' => 'Contenido no encontrado en papelera']);
        }

        $contentItem->forceDelete();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido eliminado permanentemente']);
        }

        return redirect('/admin/contents/trash?deleted=1');
    }

    /**
     * Vacia toda la papelera.
     */
    public function emptyTrash(Request $request)
    {
        $count = Content::onlyTrashed()->count();
        Content::onlyTrashed()->forceDelete();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => "Se eliminaron $count contenido(s) permanentemente"]);
        }

        return redirect('/admin/contents/trash?emptied=1');
    }

    /**
     * Genera un slug a partir de un titulo.
     */
    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ?: 'sin-titulo';
    }

    /**
     * Asegura que el slug sea unico.
     */
    private function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        $query = Content::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            $query = Content::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
