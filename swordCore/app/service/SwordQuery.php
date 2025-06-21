<?php

namespace App\service;

use App\model\Pagina;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Clase para construir y ejecutar consultas de contenido, similar a WP_Query.
 */
class SwordQuery
{
    /**
     * La colección de entradas (modelos Pagina) que resultaron de la consulta.
     * @var Collection|null
     */
    public ?Collection $entradas = null;

    /**
     * La entrada actual en el loop.
     * @var Pagina|null
     */
    public ?Pagina $entrada = null;

    /**
     * El número total de entradas encontradas que coinciden con los parámetros de la consulta.
     * @var int
     */
    public int $totalEntradas = 0;

    /**
     * El índice de la entrada actual que se está procesando en el loop.
     * @var int
     */
    public int $entradaActual = -1;

    /**
     * Los argumentos originales utilizados para la consulta.
     * @var array
     */
    protected array $variablesConsulta = [];

    /**
     * Constructor.
     *
     * @param array $argumentos Argumentos para la consulta.
     */
    public function __construct(array $argumentos = [])
    {
        $this->variablesConsulta = $argumentos;
        $this->consultar($this->variablesConsulta);
    }

    /**
     * Ejecuta la consulta a la base de datos.
     *
     * @param array $argumentos
     * @return void
     */
    public function consultar(array $argumentos): void
    {
        $query = Pagina::query();
        $this->construirConsulta($query, $argumentos);
        $this->entradas = $query->get();
        $this->totalEntradas = $this->entradas->count();
    }

    /**
     * Construye la consulta Eloquent a partir de los argumentos.
     *
     * @param Builder $query
     * @param array $argumentos
     * @return void
     */
    protected function construirConsulta(Builder $query, array $argumentos): void
    {
        // Por defecto, solo contenido publicado
        $query->where('estado', $argumentos['post_status'] ?? 'publicado');

        // Filtrar por tipo de contenido (post_type)
        if (!empty($argumentos['post_type'])) {
            $query->where('tipocontenido', $argumentos['post_type']);
        }

        // Filtrar por ID de página/post
        if (!empty($argumentos['p'])) {
            $query->where('id', (int) $argumentos['p']);
        }

        // Filtrar por slug de página/post
        if (!empty($argumentos['name'])) {
            $query->where('slug', $argumentos['name']);
        }

        // Paginación
        $posts_per_page = (int) ($argumentos['posts_per_page'] ?? -1);
        if ($posts_per_page > 0) {
            $paged = (int) ($argumentos['paged'] ?? 1);
            $offset = ($paged - 1) * $posts_per_page;
            $query->limit($posts_per_page)->offset($offset);
        }
    }

    /**
     * Determina si hay más entradas para mostrar en el loop.
     *
     * @return bool
     */
    public function havePost(): bool
    {
        return $this->entradaActual + 1 < $this->totalEntradas;
    }

    /**
     * Prepara la siguiente entrada para ser usada en el loop.
     *
     * @return void
     */
    public function thePost(): void
    {
        if ($this->havePost()) {
            $this->entradaActual++;
            $this->entrada = $this->entradas[$this->entradaActual];
        }
    }
}
