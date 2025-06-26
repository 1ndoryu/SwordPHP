<?php

namespace app\controller;

use app\model\Option;
use Illuminate\Database\Capsule\Manager as Capsule; // <-- AÑADIDO: Importar Capsule para el query builder.
use support\Request;
use support\Response;
use support\Log;
use Throwable;
use Webman\Redis;

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
            // --- INICIO DE LA CORRECCIÓN ---
            // Se utiliza el query builder en lugar del modelo Eloquent para la escritura.
            // Esto evita el conflicto con el 'cast' del modelo `Option`, que espera un
            // array de PHP pero recibe valores primitivos (string, bool, int).
            // Aquí, codificamos manualmente cada valor a JSON, lo cual es seguro para la
            // columna `jsonb` de la base de datos. Las lecturas seguirán usando el modelo.
            foreach ($options_to_update as $key => $value) {
                Capsule::table('options')->updateOrInsert(
                    ['key' => $key],
                    ['value' => json_encode($value)] // Codificación manual a JSON.
                );
            }
            // --- FIN DE LA CORRECCIÓN ---

            // Invalidar el caché de opciones en Redis para forzar una recarga en la próxima petición.
            Redis::del('sword_options');
            Log::channel('options')->info('Caché de opciones invalidado tras la actualización.', [
                'admin_id' => $request->user->id
            ]);

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