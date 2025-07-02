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
            $jophiel_url = rtrim($config['base_url'], '/') . '/v1/feed/' . $user->id;

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

            // Estructura de paginación para consistencia del cliente
            $paginated_response = [
                'current_page'   => 1,
                'data'           => $ordered_contents,
                'first_page_url' => null,
                'from'           => 1,
                'last_page'      => 1,
                'last_page_url'  => null,
                'links'          => [],
                'next_page_url'  => null,
                'path'           => $request->path(),
                'per_page'       => count($ordered_contents),
                'prev_page_url'  => null,
                'to'             => count($ordered_contents),
                'total'          => count($ordered_contents),
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

            $contents = Content::where('type', 'audio_sample')
                ->where('status', 'published')
                ->latest()
                ->paginate($per_page);

            return api_response(true, 'Jophiel unavailable. Sending latest content feed.', $contents->toArray());
        } catch (Throwable $e) {
            return api_response(false, 'An internal error occurred while fetching fallback feed.', null, 500);
        }
    }
}
