<?php
// NUEVO ARCHIVO: app/controller/SearchController.php

namespace app\controller;

use app\model\Content;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class SearchController
{
    /**
     * Hybrid search endpoint combining PostgreSQL full-text relevance with Jophiel personalisation.
     * Mirrors the contract described in the public documentation.
     */
    public function search(Request $request): Response
    {
        // --- 1) Validación de parámetros ---
        $term = trim((string) $request->get('q', ''));
        $user_id_param = (int) $request->get('user_id', 0);
        $page = max(1, (int) $request->get('page', 1));
        $per_page = (int) $request->get('per_page', 20);
        $per_page = max(1, min($per_page, 100));

        if ($term === '') {
            return api_response(false, 'El parámetro de búsqueda "q" es obligatorio.', null, 400);
        }

        if ($user_id_param <= 0) {
            return api_response(false, 'El parámetro "user_id" es obligatorio y debe ser mayor a 0.', null, 400);
        }

        $config = config('jophiel.api');

        try {
            // --- 2) Construir URL para Jophiel ---
            $query_params = http_build_query([
                'q'        => $term,
                'user_id'  => $user_id_param,
                'page'     => $page,
                'per_page' => $per_page,
            ]);

            $jophiel_url = rtrim($config['base_url'], '/') . '/v1/search?' . $query_params;

            $context = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => $config['timeout'],
                    'header'  => "Accept: application/json\r\n",
                ],
            ]);

            $body = @file_get_contents($jophiel_url, false, $context);

            // Obtener status code
            $status_code = 0;
            if (isset($http_response_header[0]) && preg_match('#HTTP/\\d+\.\\d+\\s+(\\d{3})#', $http_response_header[0], $matches)) {
                $status_code = (int) $matches[1];
            }

            if ($body === false || $status_code !== 200) {
                Log::channel('content')->error('Jophiel devolvió un error para la búsqueda', [
                    'status' => $status_code,
                    'body'   => $body ?: 'empty response',
                    'term'   => $term,
                    'user_id' => $user_id_param,
                ]);
                return $this->fallbackSearch($request, $term, $user_id_param, $page, $per_page);
            }

            $data = json_decode($body, true);

            // Validar estructura básica
            if (!isset($data['sample_ids']) || !is_array($data['sample_ids'])) {
                Log::channel('content')->warning('Respuesta inesperada de Jophiel para búsqueda', [
                    'body' => $data,
                ]);
                return $this->fallbackSearch($request, $term, $user_id_param, $page, $per_page);
            }

            // Reescribir URLs de paginación para apuntar a la ruta local (/search)
            if (isset($data['pagination'])) {
                $buildLocalUrl = function (?string $remoteUrl) use ($request) {
                    if (!$remoteUrl) return null;
                    $qs = parse_url($remoteUrl, PHP_URL_QUERY);
                    return $request->path() . ($qs ? '?' . $qs : '');
                };

                $data['pagination']['next_page_url'] = $buildLocalUrl($data['pagination']['next_page_url'] ?? null);
                $data['pagination']['prev_page_url'] = $buildLocalUrl($data['pagination']['prev_page_url'] ?? null);
            }

            return api_response(true, 'Search results retrieved successfully.', $data);
        } catch (Throwable $e) {
            Log::channel('content')->error('Excepción crítica en search', ['error' => $e->getMessage()]);
            return $this->fallbackSearch($request, $term, $user_id_param, $page, $per_page);
        }
    }

    /**
     * Fallback local search usando ILIKE sobre slug y título cuando Jophiel no está disponible.
     */
    private function fallbackSearch(Request $request, string $term, int $user_id, int $page, int $per_page): Response
    {
        try {
            $query = Content::query()
                ->where('status', 'published')
                ->where('type', 'audio_sample')
                ->where(function ($q) use ($term) {
                    $q->where('slug', 'ilike', "%{$term}%")
                      ->orWhere('content_data->title', 'ilike', "%{$term}%");
                });

            $paginator = $query->paginate($per_page, ['id'], 'page', $page);

            $sample_ids = $paginator->items();
            $sample_ids = array_map(fn($c) => $c->id, $sample_ids);

            $response = [
                'user_id'      => $user_id,
                'generated_at' => gmdate('c'),
                'sample_ids'   => $sample_ids,
                'pagination'   => [
                    'current_page'   => $paginator->currentPage(),
                    'per_page'       => $paginator->perPage(),
                    'total'          => $paginator->total(),
                    'last_page'      => $paginator->lastPage(),
                    'next_page_url'  => $paginator->nextPageUrl() ? $request->path() . '?' . parse_url($paginator->nextPageUrl(), PHP_URL_QUERY) : null,
                    'prev_page_url'  => $paginator->previousPageUrl() ? $request->path() . '?' . parse_url($paginator->previousPageUrl(), PHP_URL_QUERY) : null,
                ],
            ];

            return api_response(true, 'Jophiel unavailable. Local search fallback results.', $response);
        } catch (Throwable $e) {
            return api_response(false, 'An internal error occurred while performing fallback search.', null, 500);
        }
    }
} 