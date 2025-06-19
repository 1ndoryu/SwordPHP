<?php

namespace App\service;

use App\model\Pagina;
use Illuminate\Support\Str;
use support\exception\BusinessException;
use Webman\Exception\NotFoundException;

/**
 * Class PaginaService
 * @package App\service
 */
class PaginaService
{
    /**
     * Obtiene todas las páginas con estado 'publicado'.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function obtenerPaginasPublicadas()
    {
        return Pagina::where('tipocontenido', 'pagina')->where('estado', 'publicado')->get();
    }

    /**
     * Obtiene una lista paginada de páginas.
     *
     * @param int $porPagina
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function obtenerPaginasPaginadas(int $porPagina = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Pagina::with('autor')->latest()->paginate($porPagina);
    }

    /**
     * Encuentra una página por su ID.
     *
     * @param int $id
     * @return Pagina
     * @throws NotFoundException
     */
    public function obtenerPaginaPorId(int $id): Pagina
    {
        // La relación 'metas' ya no existe. Los metadatos están en la columna 'metadata'.
        $pagina = Pagina::find($id);

        if (!$pagina) {
            throw new NotFoundException('Página no encontrada.');
        }
        return $pagina;
    }

    /**
     * Elimina una página por su ID.
     *
     * @param int $id
     * @return bool|null
     * @throws NotFoundException
     */
    public function eliminarPagina(int $id): ?bool
    {
        $pagina = $this->obtenerPaginaPorId($id);
        // Los metadatos en la columna JSONB se eliminan junto con la página.
        // La línea que borraba la relación ya no es necesaria.
        return $pagina->delete();
    }

    /**
     * Crea una nueva página.
     *
     * @param array $datos
     * @return Pagina
     * @throws BusinessException
     */
    public function crearPagina(array $datos): Pagina
    {
        $this->validarDatos($datos);

        $datos['slug'] = $this->generarSlug($datos['titulo']);
        $datos['idautor'] = idUsuarioActual();
        $datos['tipocontenido'] = 'pagina';

        return Pagina::create($datos);
    }

    /**
     * Actualiza una página existente.
     *
     * @param Pagina $pagina
     * @param array $datos
     * @return bool
     */
    public function actualizarPagina(Pagina $pagina, array $datos): bool
    {
        $pagina->fill($datos);

        // Determina el slug base. Prioriza el campo 'slug' del formulario.
        // Si está vacío, usa el título de la página como base.
        $baseParaSlug = !empty($datos['slug']) ? $datos['slug'] : $pagina->titulo;

        $pagina->slug = $this->asegurarSlugUnico($baseParaSlug, $pagina->id);

        return $pagina->save();
    }

    /**
     * Genera un slug para un nuevo registro.
     *
     * @param string $titulo
     * @return string
     */
    private function generarSlug(string $titulo): string
    {
        return $this->asegurarSlugUnico($titulo);
    }

    /**
     * Sanitiza un texto base y asegura que el slug resultante sea único en la tabla 'paginas'.
     *
     * @param string $textoBase El texto a convertir en slug (ej: un título o un slug personalizado).
     * @param int|null $idExcluir El ID del registro a excluir de la comprobación de unicidad (usado en actualizaciones).
     * @return string El slug único y sanitizado.
     */
    private function asegurarSlugUnico(string $textoBase, ?int $idExcluir = null): string
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

    /**
     * Encuentra una página publicada por su slug.
     *
     * @param string $slug
     * @return Pagina
     * @throws NotFoundException
     */
    public function obtenerPaginaPublicadaPorSlug(string $slug): Pagina
    {
        $pagina = Pagina::where('slug', $slug)
            ->where('estado', 'publicado')
            ->first();

        if (!$pagina) {
            throw new NotFoundException('Página no encontrada.');
        }
        return $pagina;
    }

    /**
     * Valida los datos de entrada.
     * @param array $datos
     * @throws BusinessException
     */
    private function validarDatos(array $datos)
    {
        if (empty($datos['titulo'])) {
            throw new BusinessException('El campo título es obligatorio.');
        }
    }
}
