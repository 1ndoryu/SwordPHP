<?php

namespace app\controller;

use app\model\Option;
use support\Request;
use support\Response;
use support\Log;
use Throwable;

class OptionController
{
    /**
     * Retrieve all options.
     * This endpoint is public. It transforms the key-value pairs from the DB
     * into a single flat object for easy consumption by the client.
     *
     * @return Response
     */
    public function index(): Response
    {
        try {
            $options = Option::all()->pluck('value', 'key');
            return api_response(true, 'Options retrieved successfully.', $options->toArray());
        } catch (Throwable $e) {
            Log::channel('options')->error('Error retrieving options', ['error' => $e->getMessage()]);
            return api_response(false, 'An internal error occurred.', null, 500);
        }
    }

    /**
     * Batch update for options.
     * Expects a JSON object of key-value pairs.
     * Only accessible by administrators.
     *
     * @param Request $request
     * @return Response
     */
    public function updateBatch(Request $request): Response
    {
        $options_to_update = $request->post();

        if (empty($options_to_update) || !is_array($options_to_update)) {
            return api_response(false, 'Invalid data provided. Expected a JSON object of options.', null, 400);
        }

        try {
            foreach ($options_to_update as $key => $value) {
                Option::updateOrCreate(
                    ['key' => $key],
                    ['value' => is_array($value) ? json_encode($value) : $value]
                );
            }

            Log::channel('options')->info('Opciones actualizadas por administrador', [
                'admin_id' => $request->user->id,
                'updated_keys' => array_keys($options_to_update)
            ]);

            // Return all current options after update
            $all_options = Option::all()->pluck('value', 'key');
            return api_response(true, 'Options updated successfully.', $all_options->toArray());
        } catch (Throwable $e) {
            Log::channel('options')->error('Error updating options', [
                'error' => $e->getMessage(),
                'admin_id' => $request->user->id
            ]);
            return api_response(false, 'An internal error occurred while updating options.', null, 500);
        }
    }
}
