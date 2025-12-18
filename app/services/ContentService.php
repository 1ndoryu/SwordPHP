<?php

namespace app\services;

use app\model\Content;

/**
 * Servicio de lógica de negocio para Contenidos.
 * 
 * Centraliza operaciones CRUD compartidas entre controladores Admin y API.
 * Permite que los controladores solo manejen request/response.
 */
class ContentService
{
    /**
     * Número de elementos por página por defecto.
     */
    private const ITEMS_PER_PAGE = 15;

    /**
     * Genera un slug URL-friendly a partir de un título.
     * 
     * @param string $titulo Título del contenido
     * @return string Slug generado
     */
    public function generarSlug(string $titulo): string
    {
        $slug = mb_strtolower($titulo);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ?: 'sin-titulo';
    }

    /**
     * Asegura que el slug sea único en la base de datos.
     * 
     * @param string $slug Slug a verificar
     * @param int|null $excluirId ID de contenido a excluir (para actualizaciones)
     * @return string Slug único
     */
    public function asegurarSlugUnico(string $slug, ?int $excluirId = null): string
    {
        $slugOriginal = $slug;
        $contador = 1;

        $query = Content::where('slug', $slug);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        while ($query->exists()) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
            $query = Content::where('slug', $slug);
            if ($excluirId) {
                $query->where('id', '!=', $excluirId);
            }
        }

        return $slug;
    }

    /**
     * Procesa los metadatos del formulario en un array estructurado.
     * 
     * @param array $metaKeys Claves de los metadatos
     * @param array $metaValues Valores de los metadatos
     * @param array $metaIsJson Indica si cada valor es JSON
     * @return array Metadatos procesados
     */
    public function procesarMetadatos(array $metaKeys, array $metaValues, array $metaIsJson): array
    {
        $metadatos = [];

        foreach ($metaKeys as $index => $key) {
            $key = trim($key);
            if (empty($key) || in_array($key, ['title', 'content'])) {
                continue;
            }

            $value = $metaValues[$index] ?? '';
            $isJson = ($metaIsJson[$index] ?? '0') === '1';

            if ($isJson) {
                $decoded = json_decode($value, true);
                $metadatos[$key] = $decoded !== null ? $decoded : $value;
            } else {
                $metadatos[$key] = $value;
            }
        }

        return $metadatos;
    }

    /**
     * Procesa datos de imagen destacada.
     * 
     * @param string|null $id ID del medio
     * @param string|null $url URL del medio
     * @return array|null Datos estructurados o null si no hay imagen
     */
    public function procesarImagenDestacada(?string $id, ?string $url): ?array
    {
        if (empty($id) || empty($url)) {
            return null;
        }

        return [
            'id' => (int) $id,
            'url' => $url
        ];
    }

    /**
     * Construye el array content_data completo para guardar.
     * 
     * @param string $titulo Título del contenido
     * @param string $cuerpo Cuerpo/contenido principal
     * @param array $metadatos Metadatos adicionales
     * @param array|null $imagenDestacada Datos de imagen destacada
     * @return array Content data estructurado
     */
    public function construirContentData(
        string $titulo,
        string $cuerpo,
        array $metadatos = [],
        ?array $imagenDestacada = null
    ): array {
        $contentData = [
            'title' => $titulo,
            'content' => $cuerpo
        ];

        // Agregar metadatos
        foreach ($metadatos as $key => $value) {
            $contentData[$key] = $value;
        }

        // Agregar imagen destacada si existe
        if ($imagenDestacada !== null) {
            $contentData['featured_image'] = $imagenDestacada;
        }

        return $contentData;
    }

    /**
     * Crea un nuevo contenido.
     * 
     * @param array $datos Datos del contenido
     * @return Content Contenido creado
     */
    public function crear(array $datos): Content
    {
        $slug = $datos['slug'] ?? '';
        $titulo = $datos['content_data']['title'] ?? '';

        // Generar slug si está vacío
        if (empty($slug)) {
            $slug = $this->generarSlug($titulo);
        }

        // Asegurar slug único
        $slug = $this->asegurarSlugUnico($slug);

        return Content::create([
            'slug' => $slug,
            'type' => $datos['type'] ?? 'post',
            'status' => $datos['status'] ?? 'draft',
            'user_id' => $datos['user_id'],
            'content_data' => $datos['content_data']
        ]);
    }

    /**
     * Actualiza un contenido existente.
     * 
     * @param Content $contenido Instancia del contenido
     * @param array $datos Datos a actualizar
     * @return Content Contenido actualizado
     */
    public function actualizar(Content $contenido, array $datos): Content
    {
        $slug = $datos['slug'] ?? $contenido->slug;
        $titulo = $datos['content_data']['title'] ?? '';

        // Generar slug si está vacío
        if (empty($slug)) {
            $slug = $this->generarSlug($titulo);
        }

        // Asegurar slug único (excluyendo el actual)
        if ($slug !== $contenido->slug) {
            $slug = $this->asegurarSlugUnico($slug, $contenido->id);
        }

        $contenido->slug = $slug;
        $contenido->status = $datos['status'] ?? $contenido->status;
        $contenido->content_data = $datos['content_data'];
        $contenido->save();

        return $contenido;
    }

    /**
     * Envía un contenido a la papelera (soft delete).
     * 
     * @param int $id ID del contenido
     * @return bool True si se eliminó, false si no se encontró
     */
    public function enviarAPapelera(int $id): bool
    {
        $contenido = Content::find($id);
        if (!$contenido) {
            return false;
        }

        $contenido->delete();
        return true;
    }

    /**
     * Restaura un contenido de la papelera.
     * 
     * @param int $id ID del contenido
     * @return bool True si se restauró, false si no se encontró
     */
    public function restaurar(int $id): bool
    {
        $contenido = Content::onlyTrashed()->find($id);
        if (!$contenido) {
            return false;
        }

        $contenido->restore();
        return true;
    }

    /**
     * Elimina permanentemente un contenido de la papelera.
     * 
     * @param int $id ID del contenido
     * @return bool True si se eliminó, false si no se encontró
     */
    public function eliminarPermanentemente(int $id): bool
    {
        $contenido = Content::onlyTrashed()->find($id);
        if (!$contenido) {
            return false;
        }

        $contenido->forceDelete();
        return true;
    }

    /**
     * Vacía la papelera completa o de un tipo específico.
     * 
     * @param string|null $tipo Tipo de contenido a vaciar (null = todos)
     * @return int Cantidad de contenidos eliminados
     */
    public function vaciarPapelera(?string $tipo = null): int
    {
        $query = Content::onlyTrashed();

        if ($tipo) {
            $query->where('type', $tipo);
        }

        $cantidad = $query->count();
        $query->forceDelete();

        return $cantidad;
    }

    /**
     * Busca un contenido por ID.
     * 
     * @param int $id ID del contenido
     * @return Content|null
     */
    public function buscarPorId(int $id): ?Content
    {
        return Content::find($id);
    }

    /**
     * Busca un contenido en papelera por ID.
     * 
     * @param int $id ID del contenido
     * @return Content|null
     */
    public function buscarEnPapeleraPorId(int $id): ?Content
    {
        return Content::onlyTrashed()->find($id);
    }
}
