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
        return Pagina::where('estado', 'publicado')->get();
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
        $pagina = Pagina::find($id);
        if (!$pagina) {
            throw new NotFoundException('Página no encontrada.');
        }
        return $pagina;
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
        $datos['idautor'] = session('usuario.id'); // Asignar el autor de la sesión actual
        $datos['tipocontenido'] = 'pagina'; // Hardcoded por ahora

        return Pagina::create($datos);
    }

    /**
     * Actualiza una página existente.
     *
     * @param int $id
     * @param array $datos
     * @return Pagina
     * @throws BusinessException|NotFoundException
     */
    public function actualizarPagina(Pagina $pagina, array $datos): bool
    {
        // Asignación masiva de los datos validados
        $pagina->fill($datos);

        // Generar slug si el título ha cambiado o si no existía un slug previo
        if ($pagina->isDirty('titulo') || empty($pagina->slug)) {
            $pagina->slug = \Illuminate\Support\Str::slug($pagina->titulo);
        }

        return $pagina->save();
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
        return $pagina->delete();
    }

    /**
     * Genera un slug único para un título.
     * Si el slug ya existe, le añade un sufijo numérico.
     *
     * @param string $titulo
     * @return string
     */
    private function generarSlug(string $titulo): string
    {
        $slug = Str::slug($titulo);
        $slugOriginal = $slug;
        $contador = 1;

        while (Pagina::where('slug', $slug)->exists()) {
            $slug = "{$slugOriginal}-{$contador}";
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

        // Aquí se podrían añadir más validaciones en el futuro.
    }
}
