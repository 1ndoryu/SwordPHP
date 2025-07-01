<?php
// NUEVO ARCHIVO: app/controller/FeedController.php

namespace app\controller;

use app\model\Content;
use support\Request;
use support\Response;
use support\Log;
use Throwable;
use Workerman\Http\Client;

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
        $user = $request->user;
        $config = config('jophiel.api');

        try {
            $http = new Client();
            $jophiel_url = rtrim($config['base_url'], '/') . '/v1/feed/' . $user->id;

            // Make an async HTTP request to Jophiel
            $http->get($jophiel_url, [
                'timeout' => $config['timeout']
            ], function ($response) use ($request) {
                if ($response->getStatusCode() !== 200) {
                    Log::channel('content')->error('Jophiel devolvió un error para el feed', [
                        'user_id' => $request->user->id,
                        'status' => $response->getStatusCode(),
                        'body' => (string)$response->getBody()
                    ]);
                    // Fallback: return latest content if Jophiel fails
                    $this->sendFallbackFeed($request);
                    return;
                }

                $data = json_decode((string)$response->getBody(), true);
                $sample_ids = $data['sample_ids'] ?? [];

                if (empty($sample_ids)) {
                    $request->connection->send(api_response(true, 'Feed is empty.', ['data' => []]));
                    return;
                }

                // Fetch content from our DB
                $contents = Content::whereIn('id', $sample_ids)
                    ->where('status', 'published')
                    ->get()
                    ->keyBy('id');

                // Reorder the results to match Jophiel's recommendation order
                $ordered_contents = [];
                foreach ($sample_ids as $id) {
                    if (isset($contents[$id])) {
                        $ordered_contents[] = $contents[$id];
                    }
                }

                // Mimic pagination structure for client consistency
                $paginated_response = [
                    'current_page' => 1,
                    'data' => $ordered_contents,
                    'first_page_url' => null,
                    'from' => 1,
                    'last_page' => 1,
                    'last_page_url' => null,
                    'links' => [],
                    'next_page_url' => null,
                    'path' => $request->path(),
                    'per_page' => count($ordered_contents),
                    'prev_page_url' => null,
                    'to' => count($ordered_contents),
                    'total' => count($ordered_contents),
                ];

                $request->connection->send(api_response(true, 'Feed retrieved successfully.', $paginated_response));
            }, function ($exception) use ($request) {
                Log::channel('content')->error('No se pudo conectar con Jophiel para obtener el feed', [
                    'user_id' => $request->user->id,
                    'error' => $exception->getMessage()
                ]);
                // Fallback: return latest content if Jophiel fails
                $this->sendFallbackFeed($request);
            });

            // Since the response is now async, we return an empty response here.
            // The actual response is sent via $request->connection->send().
            return new Response(200);
        } catch (Throwable $e) {
            Log::channel('content')->error('Excepción crítica en getFeed', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Sends a generic feed of the latest content as a fallback mechanism.
     *
     * @param Request $request
     */
    private function sendFallbackFeed(Request $request): void
    {
        try {
            $per_page = (int) $request->get('per_page', 20);
            $contents = Content::where('type', 'audio_sample')
                ->where('status', 'published')
                ->latest()
                ->paginate($per_page);

            $request->connection->send(api_response(true, 'Jophiel unavailable. Sending latest content feed.', $contents->toArray()));
        } catch (Throwable $e) {
            $request->connection->send(api_response(false, 'An internal error occurred while fetching fallback feed.', null, 500));
        }
    }
}
