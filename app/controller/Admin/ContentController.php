<?php

namespace app\controller\Admin;

use support\Request;
use app\model\Content;
use app\services\PostTypeRegistry;
use app\services\ContentService;

/**
 * Controlador para la gestión de contenidos en el panel admin.
 * 
 * Solo maneja request/response, delegando lógica de negocio a ContentService.
 * Sigue el principio Single Responsibility (SRP).
 */
class ContentController
{
    private const ITEMS_PER_PAGE = 15;
    private ContentService $contentService;

    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    /**
     * Obtiene el Post Type desde la URL actual.
     */
    private function obtenerPostTypeDesdeUrl(Request $request, ?string $typeParam = null): ?string
    {
        if ($typeParam && $typeParam !== 'contents') {
            if (PostTypeRegistry::existe($typeParam)) {
                return $typeParam;
            }
            if (Content::where('type', $typeParam)->exists()) {
                return $typeParam;
            }
        }

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
     * Extrae parámetros ID y tipo de los argumentos de ruta.
     */
    private function extraerParametros(?string $type = null, ?int $id = null): array
    {
        if ($id === null && is_numeric($type)) {
            return [null, (int) $type];
        }
        return [$type, $id];
    }

    /**
     * Obtiene datos comunes de la sesión del usuario.
     */
    private function obtenerDatosSesion(Request $request): array
    {
        return [
            'username' => $request->session()->get('admin_username') ?? 'Admin',
            'userId' => $request->session()->get('admin_user_id')
        ];
    }

    /**
     * Muestra el listado de contenidos con filtros y paginación.
     */
    public function index(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $postTypeConfig = $postType ? PostTypeRegistry::get($postType) : null;
        $sesion = $this->obtenerDatosSesion($request);

        $page = (int) $request->get('page', 1);
        $status = $request->get('status', '');
        $search = $request->get('search', '');

        $query = Content::with('user')->orderBy('created_at', 'desc');

        if ($postType) {
            $query->where('type', $postType);
        }

        if (!empty($status)) {
            $query->where('status', $status);
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

        $types = $postType ? [] : Content::select('type')->distinct()->pluck('type')->toArray();
        $baseUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
        $titulo = $postTypeConfig ? $postTypeConfig['nombre'] : 'Contenidos';

        if ($request->header('accept') === 'application/json') {
            return json([
                'contents' => $contents,
                'pagination' => [
                    'current' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $total,
                    'per_page' => self::ITEMS_PER_PAGE
                ],
                'post_type' => $postType,
                'post_type_config' => $postTypeConfig,
                'filters' => [
                    'status' => $status,
                    'search' => $search
                ]
            ]);
        }

        $content = render_view('admin/pages/contents/index', [
            'contents' => $contents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'types' => $types,
            'postType' => $postType,
            'postTypeConfig' => $postTypeConfig,
            'baseUrl' => $baseUrl,
            'filters' => ['status' => $status, 'search' => $search]
        ]);

        return render_view('admin/layouts/layout', [
            'title' => $titulo,
            'user' => $sesion['username'],
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Muestra el formulario de creación de contenido.
     */
    public function create(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type) ?? 'post';
        $postTypeConfig = PostTypeRegistry::get($postType);
        $sesion = $this->obtenerDatosSesion($request);

        $content = render_view('admin/pages/contents/editor', [
            'mode' => 'create',
            'type' => $postType,
            'postType' => $postType,
            'postTypeConfig' => $postTypeConfig,
            'baseUrl' => "/admin/{$postType}",
            'content' => null
        ]);

        if ($request->header('accept') === 'application/json') {
            return json([
                'postType' => $postType,
                'postTypeConfig' => $postTypeConfig,
                'mode' => 'create'
            ]);
        }

        $nombreSingular = $postTypeConfig['nombreSingular'] ?? 'Contenido';
        return render_view('admin/layouts/layout', [
            'title' => "Nueva {$nombreSingular}",
            'user' => $sesion['username'],
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
        $sesion = $this->obtenerDatosSesion($request);

        $metadatos = $this->contentService->procesarMetadatos(
            $request->post('meta_keys', []),
            $request->post('meta_values', []),
            $request->post('meta_is_json', [])
        );

        $imagenDestacada = $this->contentService->procesarImagenDestacada(
            $request->post('featured_image_id', ''),
            $request->post('featured_image_url', '')
        );

        $contentData = $this->contentService->construirContentData(
            $request->post('title', ''),
            $request->post('content', ''),
            $metadatos,
            $imagenDestacada
        );

        $nuevoContenido = $this->contentService->crear([
            'slug' => $request->post('slug', ''),
            'type' => $postType,
            'status' => $request->post('status', 'draft'),
            'user_id' => $sesion['userId'],
            'content_data' => $contentData
        ]);

        if ($request->header('accept') === 'application/json') {
            return json([
                'success' => true,
                'content' => $nuevoContenido,
                'message' => 'Contenido creado correctamente'
            ]);
        }

        return redirect("/admin/{$postType}/{$nuevoContenido->id}/edit?saved=1");
    }

    /**
     * Muestra el formulario de edición de contenido.
     */
    public function edit(Request $request, ?string $type = null, ?int $id = null)
    {
        [$type, $id] = $this->extraerParametros($type, $id);
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $sesion = $this->obtenerDatosSesion($request);

        $contentItem = $this->contentService->buscarPorId($id);
        if (!$contentItem) {
            if ($request->header('accept') === 'application/json') {
                return json(['error' => 'not_found'], 404);
            }
            $redirectUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
            return redirect($redirectUrl . '?error=not_found');
        }

        $postType = $postType ?? $contentItem->type;
        $postTypeConfig = PostTypeRegistry::get($postType);

        if ($request->header('accept') === 'application/json') {
            return json([
                'content' => $contentItem,
                'postType' => $postType,
                'postTypeConfig' => $postTypeConfig
            ]);
        }

        $content = render_view('admin/pages/contents/editor', [
            'mode' => 'edit',
            'type' => $contentItem->type,
            'postType' => $postType,
            'postTypeConfig' => $postTypeConfig,
            'baseUrl' => "/admin/{$postType}",
            'contentItem' => $contentItem,
            'saved' => $request->get('saved', 0)
        ]);

        $nombreSingular = $postTypeConfig['nombreSingular'] ?? 'Contenido';
        return render_view('admin/layouts/layout', [
            'title' => "Editar {$nombreSingular}",
            'user' => $sesion['username'],
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Actualiza un contenido existente.
     */
    public function update(Request $request, ?string $type = null, ?int $id = null)
    {
        [$type, $id] = $this->extraerParametros($type, $id);
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);

        $contentItem = $this->contentService->buscarPorId($id);
        if (!$contentItem) {
            if ($request->header('accept') === 'application/json') {
                return json(['error' => 'not_found'], 404);
            }
            $redirectUrl = $postType ? "/admin/{$postType}" : '/admin/contents';
            return redirect($redirectUrl . '?error=not_found');
        }

        $postType = $postType ?? $contentItem->type;

        $metadatos = $this->contentService->procesarMetadatos(
            $request->post('meta_keys', []),
            $request->post('meta_values', []),
            $request->post('meta_is_json', [])
        );

        $imagenDestacada = $this->contentService->procesarImagenDestacada(
            $request->post('featured_image_id', ''),
            $request->post('featured_image_url', '')
        );

        $contentData = $this->contentService->construirContentData(
            $request->post('title', ''),
            $request->post('content', ''),
            $metadatos,
            $imagenDestacada
        );

        $this->contentService->actualizar($contentItem, [
            'slug' => $request->post('slug', ''),
            'status' => $request->post('status', 'draft'),
            'type' => $postType, // Update type if needed, though usually fixed
            'content_data' => $contentData
        ]);

        if ($request->header('accept') === 'application/json') {
            return json([
                'success' => true,
                'message' => 'Contenido actualizado correctamente',
                'content' => $contentItem
            ]);
        }

        return redirect("/admin/{$postType}/{$id}/edit?saved=1");
    }

    /**
     * Envía un contenido a la papelera (soft delete).
     */
    public function destroy(Request $request, ?string $type = null, ?int $id = null)
    {
        [$type, $id] = $this->extraerParametros($type, $id);
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);

        $eliminado = $this->contentService->enviarAPapelera($id);

        if (!$eliminado) {
            return json(['success' => false, 'message' => 'Contenido no encontrado']);
        }

        if ($request->header('accept') === 'application/json' || $request->isAjax()) {
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
        $sesion = $this->obtenerDatosSesion($request);

        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');

        $query = Content::onlyTrashed()->with('user')->orderBy('deleted_at', 'desc');

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

        $content = render_view('admin/pages/contents/trash', [
            'contents' => $contents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'postType' => $postType,
            'baseUrl' => $baseUrl,
            'filters' => ['search' => $search]
        ]);

        return render_view('admin/layouts/layout', [
            'title' => 'Papelera',
            'user' => $sesion['username'],
            'content' => $content,
            'currentPostType' => $postType
        ]);
    }

    /**
     * Restaura un contenido de la papelera.
     */
    public function restore(Request $request, ?string $type = null, ?int $id = null)
    {
        [$type, $id] = $this->extraerParametros($type, $id);
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);

        $restaurado = $this->contentService->restaurar($id);

        if (!$restaurado) {
            return json(['success' => false, 'message' => 'Contenido no encontrado en papelera']);
        }

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
        [$type, $id] = $this->extraerParametros($type, $id);
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);

        $eliminado = $this->contentService->eliminarPermanentemente($id);

        if (!$eliminado) {
            return json(['success' => false, 'message' => 'Contenido no encontrado en papelera']);
        }

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => 'Contenido eliminado permanentemente']);
        }

        $redirectUrl = $postType ? "/admin/{$postType}/trash" : '/admin/contents/trash';
        return redirect($redirectUrl . '?deleted=1');
    }

    /**
     * Vacía toda la papelera (o solo la de un tipo específico).
     */
    public function emptyTrash(Request $request, ?string $type = null)
    {
        $postType = $this->obtenerPostTypeDesdeUrl($request, $type);
        $cantidad = $this->contentService->vaciarPapelera($postType);

        if ($request->isAjax()) {
            return json(['success' => true, 'message' => "Se eliminaron {$cantidad} contenido(s) permanentemente"]);
        }

        $redirectUrl = $postType ? "/admin/{$postType}/trash" : '/admin/contents/trash';
        return redirect($redirectUrl . '?emptied=1');
    }
}
