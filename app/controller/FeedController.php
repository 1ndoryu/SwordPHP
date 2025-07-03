<?php
// NUEVO ARCHIVO: app/controller/FeedController.php

namespace app\controller;

use app\model\Content;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class FeedController
{
    /**
     * Retrieves the personalized content feed for the authenticated user
     * by calling the Jophiel recommendation engine.
     *
     * @param Request $request
     * @return Response
     */
    public function getFeed(Request $request): Response
    {
        $user   = $request->user;
        $config = config('jophiel.api');

        try {
            // 1) Validar y reenviar parámetros de paginación a Jophiel
            $page     = max(1, (int) $request->get('page', 1));
            $per_page = (int) $request->get('per_page', 20);
            $per_page = max(1, min($per_page, 100)); // Límite definido por Jophiel

            $query_params = http_build_query([
                'page'     => $page,
                'per_page' => $per_page,
            ]);

            $jophiel_url = rtrim($config['base_url'], '/') . '/v1/feed/' . $user->id . '?' . $query_params;

            // Petición síncrona (bloqueante) usando file_get_contents
            $context = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => $config['timeout'],
                    'header'  => "Accept: application/json\r\n",
                ],
            ]);

            $body = @file_get_contents($jophiel_url, false, $context);

            // Obtener el código de estado HTTP
            $status_code = 0;
            if (isset($http_response_header[0]) && preg_match('#HTTP/\d+\.\d+\s+(\d{3})#', $http_response_header[0], $matches)) {
                $status_code = (int) $matches[1];
            }

            if ($body === false || $status_code !== 200) {
                Log::channel('content')->error('Jophiel devolvió un error para el feed', [
                    'user_id' => $user->id,
                    'status'  => $status_code,
                    'body'    => $body ?: 'empty response',
                ]);

                // Fallback si Jophiel falla
                return $this->fallbackFeed($request);
            }

            $data       = json_decode($body, true);
            $sample_ids = $data['sample_ids'] ?? [];
            $pagination = $data['pagination'] ?? null;

            if (empty($sample_ids)) {
                return api_response(true, 'Feed is empty.', ['data' => []]);
            }

            // Fetch content de la base de datos
            $contents = Content::whereIn('id', $sample_ids)
                ->where('status', 'published')
                ->get()
                ->keyBy('id');

            // Reordenar según la recomendación de Jophiel
            $ordered_contents = [];
            foreach ($sample_ids as $id) {
                if (isset($contents[$id])) {
                    $ordered_contents[] = $contents[$id];
                }
            }

            // Reutilizar metadatos de paginación de Jophiel o generar uno simple si no está disponible
            if ($pagination) {
                // Reescribir las URLs para que apunten a la propia API de Sword (/feed)
                $buildLocalUrl = function (?string $remoteUrl) use ($request) {
                    if (!$remoteUrl) return null;
                    // Analizar query params
                    $qs = parse_url($remoteUrl, PHP_URL_QUERY);
                    return $request->path() . ($qs ? '?' . $qs : '');
                };

                $pagination['next_page_url'] = $buildLocalUrl($pagination['next_page_url'] ?? null);
                $pagination['prev_page_url'] = $buildLocalUrl($pagination['prev_page_url'] ?? null);
            } else {
                $pagination = [
                    'current_page'   => 1,
                    'per_page'       => count($ordered_contents),
                    'total'          => count($ordered_contents),
                    'last_page'      => 1,
                    'next_page_url'  => null,
                    'prev_page_url'  => null,
                ];
            }

            $paginated_response = [
                'data'       => $ordered_contents,
                'pagination' => $pagination,
            ];

            return api_response(true, 'Feed retrieved successfully.', $paginated_response);
        } catch (Throwable $e) {
            Log::channel('content')->error('Excepción crítica en getFeed', ['error' => $e->getMessage()]);
            // Fallback en caso de excepción
            return $this->fallbackFeed($request);
        }
    }

    /**
     * Devuelve un feed genérico con el contenido más reciente como mecanismo de respaldo.
     */
    private function fallbackFeed(Request $request): Response
    {
        try {
            $per_page = (int) $request->get('per_page', 20);
            $per_page = max(1, min($per_page, 100));

            $page = max(1, (int) $request->get('page', 1));

            $contents = Content::where('type', 'audio_sample')
                ->where('status', 'published')
                ->latest()
                ->paginate($per_page, ['*'], 'page', $page);

            // Convertir a la misma estructura de retorno que getFeed
            $response = [
                'data'       => $contents->items(),
                'pagination' => [
                    'current_page'   => $contents->currentPage(),
                    'per_page'       => $contents->perPage(),
                    'total'          => $contents->total(),
                    'last_page'      => $contents->lastPage(),
                    'next_page_url'  => $contents->nextPageUrl() ? $request->path() . '?' . parse_url($contents->nextPageUrl(), PHP_URL_QUERY) : null,
                    'prev_page_url'  => $contents->previousPageUrl() ? $request->path() . '?' . parse_url($contents->previousPageUrl(), PHP_URL_QUERY) : null,
                ],
            ];

            return api_response(true, 'Jophiel unavailable. Sending latest content feed.', $response);
        } catch (Throwable $e) {
            return api_response(false, 'An internal error occurred while fetching fallback feed.', null, 500);
        }
    }
}
