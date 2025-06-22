<?php

namespace App\service;

use App\model\Pagina;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Clase para construir y ejecutar consultas de contenido, similar a WP_Query.
 */
class SwordQuery
{
    public ?Collection $entradas = null;
    public ?Pagina $entrada = null;
    public int $totalEntradas = 0;
    public int $entradaActual = -1;
    protected array $variablesConsulta = [];

    public function __construct(array $argumentos = [])
    {
        $this->variablesConsulta = $this->normalizarArgumentos($argumentos);
        $this->consultar($this->variablesConsulta);
    }

    private function normalizarArgumentos(array $args): array
    {
        $defaults = [
            'post_type' => 'pagina',
            'post_status' => 'publicado',
            'posts_per_page' => 10,
            'paged' => 1,
            'sort_by' => 'created_at',
            'order' => 'desc',
            'include' => [],
            'q' => '',
            'id_autor' => null,
            'meta_query' => []
        ];

        $parsed = array_merge($defaults, $args);
        
        if (is_string($parsed['include'])) {
            $parsed['include'] = array_map('trim', explode(',', $parsed['include']));
        }

        return $parsed;
    }

    public function consultar(array $argumentos): void
    {
        $query = Pagina::query();
        $this->construirConsulta($query, $argumentos);

        // Utilizamos el paginador de Eloquent para obtener tanto los items como el total.
        $paginador = $query->paginate(
            $argumentos['posts_per_page'],
            ['*'],
            'page',
            $argumentos['paged']
        );
        
        $this->entradas = $paginador->getCollection();
        $this->totalEntradas = $paginador->total();
    }

    protected function construirConsulta(Builder $query, array $argumentos): void
    {
        // Eager Loading (Include)
        if (!empty($argumentos['include'])) {
            $allowedRelations = ['autor'];
            $validRelations = array_intersect($argumentos['include'], $allowedRelations);
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }

        $query->where('estado', $argumentos['post_status']);
        $query->where('tipocontenido', $argumentos['post_type']);

        // Filtrado por autor
        if (!empty($argumentos['id_autor'])) {
            $query->where('idautor', (int)$argumentos['id_autor']);
        }
        
        // Búsqueda de texto (simple)
        if (!empty($argumentos['q'])) {
            $searchTerm = '%' . $argumentos['q'] . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('titulo', 'ILIKE', $searchTerm)
                  ->orWhere('contenido', 'ILIKE', $searchTerm);
            });
        }

        // Filtrado por metadata (meta_query)
        if (!empty($argumentos['meta_query']) && is_array($argumentos['meta_query'])) {
            foreach ($argumentos['meta_query'] as $meta) {
                 if (isset($meta['key'], $meta['value'])) {
                    // Para PostgreSQL, se usa whereJsonContains para buscar dentro del JSON
                    $query->whereJsonContains("metadata->{$meta['key']}", $meta['value']);
                 }
            }
        }

        // Ordenamiento
        $sortBy = in_array($argumentos['sort_by'], ['created_at', 'updated_at', 'titulo']) ? $argumentos['sort_by'] : 'created_at';
        $order = strtolower($argumentos['order']) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $order);
    }
    
    /**
     * Determina si hay más entradas para mostrar en el loop.
     * @return bool
     */
    public function havePost(): bool
    {
        return $this->entradaActual + 1 < $this->totalEntradas;
    }

    /**
     * Prepara la siguiente entrada para ser usada en el loop.
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