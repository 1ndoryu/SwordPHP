<?php

namespace app\controller\Admin;

use support\Request;
use app\model\Content;
use app\services\PostTypeRegistry;

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
     * Obtiene el Post Type desde la URL actual.
     * Acepta tipos registrados y tipos que existan en la BD.
     * 
     * @param Request $request
     * @param string|null $typeParam Tipo pasado como parametro de ruta
     * @return string|null
     */
    private function obtenerPostTypeDesdeUrl(Request $request, ?string $typeParam = null): ?string
    {
        // Si viene como parametro de ruta, usarlo directamente
        if ($typeParam && $typeParam !== 'contents') {
            // Verificar si existe en el registro o en la BD
            if (PostTypeRegistry::existe($typeParam)) {
                return $typeParam;
            }
            // Verificar si hay contenidos con ese tipo en la BD
            if (Content::where('type', $typeParam)->exists()) {
                return $typeParam;
            }
        }

        // Fallback: extraer de la URL
        $path = $request->path();
        $segments = explode('/', trim($path, '/'));

        if (count($segments) >= 2) {
            $posibleTipo = $segments[1];
            if ($posibleTipo !== 'contents' && PostTypeRegistry::existe($posibleTipo)) {
                return $posibleTipo;
            }
        }

        return null;
    }

    /**
     * Muestra el listado de contenidos con filtros y paginacion.
     * 
     * @param Request $request
     * @param string|null $type Tipo de contenido desde la ruta
     */
    public function index(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $postTypeConfig = $postType ? PostTypeRegistry::get($postType) : null;

        $page = (int) $request->get('page', 1);
        $status = $request->get('status', '');
        $search = $request->get('search', '');

        $query = Content::with('user')->orderBy('created_at', 'desc');

        // Filtrar por Post Type si viene desde una ruta específica
        if ($postType) {
            $query->where('type', $postType);
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

        // Obtener tipos unicos para el filtro (solo si no hay postType especifico)
        $types = $postType ? [] : Content::select('type')->distinct()->pluck('type')->toArray();

        $baseUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
        $titulo = $postTypeConfig ? $postTypeConfig['nombre'] : 'Contenidos';

        $viewData = [
            'contents' => $contents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'types' => $types,
            'postType' => $postType,
            'postTypeConfig' => $postTypeConfig,
            'baseUrl' => $baseUrl,
            'filters' => [
                'status' => $status,
                'search' => $search
            ]
        ];

        $content = render_view('admin/pages/contents/index', $viewData);
        return render_view('admin/layouts/layout', [
            'title' => $titulo,
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Muestra el formulario de creacion de contenido.
     */
    public function create(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type) ?? 'post';
        $postTypeConfig = PostTypeRegistry::get($postType);
        $baseUrl = "/admin/{$postType}";

        $content = render_view('admin/pages/contents/editor', [
            'mode' => 'create',
            'type' => $postType,
            'postType' => $postType,
            'postTypeConfig' => $postTypeConfig,
            'baseUrl' => $baseUrl,
            'content' => null
        ]);

        $nombreSingular = $postTypeConfig['nombreSingular'] ?? 'Contenido';
        return render_view('admin/layouts/layout', [
            'title' => "Nueva {$nombreSingular}",
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Almacena un nuevo contenido.
     */
    public function store(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type) ?? 'post';
        $userId = $request->session()->get('admin_user_id');

        $title = $request->post('title', '');
        $contentBody = $request->post('content', '');
        $slug = $request->post('slug', '');
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

        // Procesar imagen destacada
        $featuredImageId = $request->post('featured_image_id', '');
        $featuredImageUrl = $request->post('featured_image_url', '');
        if (!empty($featuredImageId) && !empty($featuredImageUrl)) {
            $contentData['featured_image'] = [
                'id' => (int) $featuredImageId,
                'url' => $featuredImageUrl
            ];
        }

        $newContent = Content::create([
            'slug' => $slug,
            'type' => $postType,
            'status' => $status,
            'user_id' => $userId,
            'content_data' => $contentData
        ]);

        return redirect("/admin/{$postType}/{$newContent->id}/edit?saved=1");
    }

    /**
     * Muestra el formulario de edicion de contenido.
     */
    public function edit(Request $request, ?string $type = null, ?int $id = null)
    {
        // Webman pasa los params en orden según la ruta, manejar ambos casos
        if ($id === null && is_numeric($type)) {
            $id = (int) $type;
            $type = null;
        }

        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $contentItem = Content::find($id);

        if (!$contentItem) {
            $redirectUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
            return redirect($redirectUrl . '?error=not_found');
        }

        // Usar el tipo del contenido si no viene de la URL
        $postType = $postType ?? $contentItem->type;
        $postTypeConfig = PostTypeRegistry::get($postType);
        $baseUrl = "/admin/{$postType}";

        $saved = $request->get('saved', 0);

        $content = render_view('admin/pages/contents/editor', [
            'mode' => 'edit',
            'type' => $contentItem->type,
            'postType' => $postType,
            'postTypeConfig' => $postTypeConfig,
            'baseUrl' => $baseUrl,
            'contentItem' => $contentItem,
            'saved' => $saved
        ]);

        $nombreSingular = $postTypeConfig['nombreSingular'] ?? 'Contenido';
        return render_view('admin/layouts/layout', [
            'title' => "Editar {$nombreSingular}",
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Actualiza un contenido existente.
     */
    public function update(Request $request, ?string $type = null, ?int $id = null)
    {
        if ($id === null && is_numeric($type)) {
            $id = (int) $type;
            $type = null;
        }

        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $contentItem = Content::find($id);

        if (!$contentItem) {
            $redirectUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
            return redirect($redirectUrl . '?error=not_found');
        }

        $postType = $postType ?? $contentItem->type;

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

        // Procesar imagen destacada
        $featuredImageId = $request->post('featured_image_id', '');
        $featuredImageUrl = $request->post('featured_image_url', '');
        if (!empty($featuredImageId) && !empty($featuredImageUrl)) {
            $contentData['featured_image'] = [
                'id' => (int) $featuredImageId,
                'url' => $featuredImageUrl
            ];
        }

        $contentItem->slug = $slug;
        $contentItem->status = $status;
        $contentItem->content_data = $contentData;
        $contentItem->save();

        return redirect("/admin/{$postType}/{$id}/edit?saved=1");
    }

    /**
     * Envia un contenido a la papelera (soft delete).
     */
    public function destroy(Request $request, ?string $type = null, ?int $id = null)
    {
        if ($id === null && is_numeric($type)) {
            $id = (int) $type;
            $type = null;
        }

        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $contentItem = Content::find($id);

        if (!$contentItem) {
            return json(['success' => false, 'message' => 'Contenido no encontrado']);
        }

        $contentItem->delete();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido enviado a la papelera']);
        }

        $redirectUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
        return redirect($redirectUrl . '?trashed=1');
    }

    /**
     * Muestra el listado de contenidos en la papelera.
     */
    public function trash(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');

        $query = Content::onlyTrashed()->with('user')->orderBy('deleted_at', 'desc');

        // Filtrar por tipo si viene especificado
        if ($postType) {
            $query->where('type', $postType);
        }

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

        $baseUrl = $postType ? "/admin/{$postType}" : '/admin/contents';

        $viewData = [
            'contents' => $contents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'postType' => $postType,
            'baseUrl' => $baseUrl,
            'filters' => [
                'search' => $search
            ]
        ];

        $content = render_view('admin/pages/contents/trash', $viewData);
        return render_view('admin/layouts/layout', [
            'title' => 'Papelera',
            'user' => $request->session()->get('admin_username') ?? 'Admin',
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Restaura un contenido de la papelera.
     */
    public function restore(Request $request, ?string $type = null, ?int $id = null)
    {
        if ($id === null && is_numeric($type)) {
            $id = (int) $type;
            $type = null;
        }

        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $contentItem = Content::onlyTrashed()->find($id);

        if (!$contentItem) {
            return json(['success' => false, 'message' => 'Contenido no encontrado en papelera']);
        }

        $contentItem->restore();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido restaurado correctamente']);
        }

        $redirectUrl = $postType ? "/admin/{$postType}/trash" : '/admin/contents/trash';
        return redirect($redirectUrl . '?restored=1');
    }

    /**
     * Elimina permanentemente un contenido de la papelera.
     */
    public function forceDestroy(Request $request, ?string $type = null, ?int $id = null)
    {
        if ($id === null && is_numeric($type)) {
            $id = (int) $type;
            $type = null;
        }

        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $contentItem = Content::onlyTrashed()->find($id);

        if (!$contentItem) {
            return json(['success' => false, 'message' => 'Contenido no encontrado en papelera']);
        }

        $contentItem->forceDelete();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido eliminado permanentemente']);
        }

        $redirectUrl = $postType ? "/admin/{$postType}/trash" : '/admin/contents/trash';
        return redirect($redirectUrl . '?deleted=1');
    }

    /**
     * Vacia toda la papelera (o solo la de un tipo específico).
     */
    public function emptyTrash(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);

        $query = Content::onlyTrashed();
        if ($postType) {
            $query->where('type', $postType);
        }

        $count = $query->count();
        $query->forceDelete();

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => "Se eliminaron $count contenido(s) permanentemente"]);
        }

        $redirectUrl = $postType ? "/admin/{$postType}/trash" : '/admin/contents/trash';
        return redirect($redirectUrl . '?emptied=1');
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
