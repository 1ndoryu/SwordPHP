<?php
// app\controller\WebhookController.php

namespace app\controller;

use app\model\Webhook;
use app\model\Content; // <-- Añadido
use app\services\JophielService; // <-- Añadido
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class WebhookController
{
    public function index(): Response
    {
        try {
            $webhooks = Webhook::all();
            return api_response(true, 'Webhooks retrieved successfully.', $webhooks->toArray());
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Error retrieving webhooks', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    public function store(Request $request): Response
    {
        $data = $request->post();
        if (empty($data['event_name']) || empty($data['target_url'])) {
            return api_response(false, 'event_name and target_url are required.', null, 400);
        }

        if (!filter_var($data['target_url'], FILTER_VALIDATE_URL)) {
            return api_response(false, 'Invalid target_url format.', null, 400);
        }

        try {
            $webhook = Webhook::create([
                'event_name' => $data['event_name'],
                'target_url' => $data['target_url'],
                'secret' => $data['secret'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            Log::channel('webhooks')->info('Nuevo webhook creado', ['id' => $webhook->id, 'event' => $webhook->event_name]);
            return api_response(true, 'Webhook created successfully.', $webhook->toArray(), 201);
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Error creating webhook', ['error' => $e->getMessage(), 'data' => $data]);
            return api_response(false, 'Could not create webhook.', null, 500);
        }
    }

    public function update(Request $request, int $id): Response
    {
        $webhook = Webhook::find($id);
        if (!$webhook) {
            return api_response(false, 'Webhook not found.', null, 404);
        }

        try {
            $webhook->update($request->post());
            Log::channel('webhooks')->info('Webhook actualizado', ['id' => $id, 'admin_id' => $request->user->id]);
            return api_response(true, 'Webhook updated successfully.', $webhook->toArray());
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Error updating webhook', ['error' => $e->getMessage(), 'id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    public function destroy(Request $request, int $id): Response
    {
        $webhook = Webhook::find($id);
        if (!$webhook) {
            return api_response(false, 'Webhook not found.', null, 404);
        }

        try {
            $webhook->delete();
            Log::channel('webhooks')->warning('Webhook eliminado', ['id' => $id, 'admin_id' => $request->user->id]);
            return new Response(204); // No Content
        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Error deleting webhook', ['error' => $e->getMessage(), 'id' => $id]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    // --- INICIO: NUEVO MÉTODO ---
    /**
     * Handles the incoming webhook from Casiel service after audio analysis.
     * This triggers the 'sample.lifecycle.created' event for Jophiel.
     *
     * @param Request $request
     * @return Response
     */
    public function handleCasielProcessed(Request $request): Response
    {
        $data = $request->post();
        $content_id = $data['content_id'] ?? null;
        $metadata = $data['metadata'] ?? null;

        if (!$content_id || !$metadata) {
            Log::channel('webhooks')->warning('Webhook de Casiel recibido con datos incompletos.', ['data' => $data]);
            return api_response(false, 'Incomplete data: content_id and metadata are required.', null, 400);
        }

        try {
            $content = Content::find($content_id);
            if (!$content) {
                Log::channel('webhooks')->error('Webhook de Casiel para un content_id inexistente.', ['content_id' => $content_id]);
                return api_response(false, 'Content not found.', null, 404);
            }

            // Update the content with the new metadata from Casiel
            $content->content_data = array_merge($content->content_data ?? [], $metadata);
            $content->save();

            Log::channel('content')->info('Metadata de Casiel guardada en el contenido.', ['content_id' => $content_id]);

            // Dispatch the event for Jophiel
            jophielEvento('sample.lifecycle.created', [
                'sample_id' => $content->id,
                'creator_id' => $content->user_id,
                'metadata' => $content->content_data // Send the full, merged metadata
            ]);
            
            return api_response(true, 'Casiel webhook processed and event dispatched to Jophiel.');

        } catch (Throwable $e) {
            Log::channel('webhooks')->error('Error crítico procesando el webhook de Casiel.', [
                'error' => $e->getMessage(),
                'content_id' => $content_id
            ]);
            return api_response(false, 'Internal server error while processing webhook.', null, 500);
        }
    }
    // --- FIN: NUEVO MÉTODO ---
}