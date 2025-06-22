<?php

namespace App\service;

use App\model\Pagina;
use Illuminate\Support\Str;
use support\exception\BusinessException;
use Webman\Exception\NotFoundException;

class PaginaService
{
    public function obtenerPaginasPublicadas()
    {
        return Pagina::where('tipocontenido', 'pagina')->where('estado', 'publicado')->get();
    }

    public function obtenerPaginasPaginadas(int $porPagina = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Pagina::with('autor')->latest()->paginate($porPagina);
    }

    public function obtenerPaginaPorId(int $id, array $relaciones = []): Pagina
    {
        $allowedRelations = ['autor']; // Lista blanca de relaciones
        $validRelations = array_intersect($relaciones, $allowedRelations);

        $query = Pagina::query();

        if (!empty($validRelations)) {
            $query->with($validRelations);
        }

        $pagina = $query->find($id);

        if (!$pagina) {
            throw new NotFoundException('Recurso no encontrado.');
        }
        return $pagina;
    }

    public function eliminarPagina(int $id): ?bool
    {
        $pagina = $this->obtenerPaginaPorId($id);
        return $pagina->delete();
    }

    public function crearPagina(array $datos): Pagina
    {
        $this->validarDatos($datos);

        $datos['slug'] = $this->generarSlug($datos['titulo']);

        // En un contexto de API, el idautor y tipocontenido DEBEN ser proporcionados por el controlador.
        // Para mantener la compatibilidad con llamadas antiguas (no-API), se establece un valor por defecto si no existen.
        if (!isset($datos['idautor'])) {
            $datos['idautor'] = idCurrentUser();
        }
        if (!isset($datos['tipocontenido'])) {
            $datos['tipocontenido'] = 'pagina';
        }

        return Pagina::create($datos);
    }

    public function actualizarPagina(Pagina $pagina, array $datos): bool
    {
        // Si se provee un nuevo slug o título, se regenera para asegurar unicidad
        if (!empty($datos['slug']) || !empty($datos['titulo'])) {
            $baseParaSlug = !empty($datos['slug']) ? $datos['slug'] : $pagina->titulo;
            $datos['slug'] = $this->asegurarSlugUnico($baseParaSlug, $pagina->id);
        }

        // Usar fill y luego save para actualizar
        $pagina->fill($datos);
        return $pagina->save();
    }

    public function asegurarSlugUnico(string $textoBase, ?int $idExcluir = null): string
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

    private function generarSlug(string $titulo): string
    {
        return $this->asegurarSlugUnico($titulo);
    }

    private function validarDatos(array $datos)
    {
        if (empty($datos['titulo'])) {
            throw new BusinessException('El campo título es obligatorio.');
        }
    }
}
