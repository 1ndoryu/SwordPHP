<?php

namespace App\service;

use App\model\Pagina;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

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

        if ($argumentos['posts_per_page'] === -1) {
            // No paginar, obtener todos los resultados
            $this->entradas = $query->get();
            $this->totalEntradas = $this->entradas->count();
        } else {
            // Utilizar el paginador de Eloquent para obtener tanto los items como el total.
            $paginador = $query->paginate(
                $argumentos['posts_per_page'],
                ['*'],
                'page',
                $argumentos['paged']
            );

            $this->entradas = $paginador->getCollection();
            $this->totalEntradas = $paginador->total();
        }
    }

    protected function construirConsulta(Builder $query, array $argumentos): void
    {
        if (!empty($argumentos['include'])) {
            $allowedRelations = ['autor'];
            $validRelations = array_intersect($argumentos['include'], $allowedRelations);
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }

        $query->where('estado', $argumentos['post_status']);
        $query->where('tipocontenido', $argumentos['post_type']);

        if (!empty($argumentos['id_autor'])) {
            $query->where('idautor', (int)$argumentos['id_autor']);
        }

        if (!empty($argumentos['q'])) {
            $searchTerm = '%' . $argumentos['q'] . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('titulo', 'ILIKE', $searchTerm)
                    ->orWhere('contenido', 'ILIKE', $searchTerm);
            });
        }

        if (!empty($argumentos['meta_query']) && is_array($argumentos['meta_query'])) {
            foreach ($argumentos['meta_query'] as $meta) {
                if (isset($meta['key'], $meta['value'])) {
                    // >>> INICIO: EL ÚNICO CAMBIO REQUERIDO <<<
                    $dbKey = \Illuminate\Support\Str::camel($meta['key']);
                    $query->where("metadata->>{$dbKey}", (string) $meta['value']);
                    // >>> FIN: EL ÚNICO CAMBIO REQUERIDO <<<
                }
            }
        }

        $sortBy = in_array($argumentos['sort_by'], ['created_at', 'updated_at', 'titulo']) ? $argumentos['sort_by'] : 'created_at';
        $order = strtolower($argumentos['order']) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $order);
    }

    public function havePost(): bool
    {
        return $this->entradaActual + 1 < $this->totalEntradas;
    }

    public function thePost(): void
    {
        if ($this->havePost()) {
            $this->entradaActual++;
            $this->entrada = $this->entradas[$this->entradaActual];
        }
    }
}
