<?php
// app/process/JophielSyncProcess.php

namespace app\process;

use Workerman\Worker;
use Workerman\Timer;
use app\model\Content;
use app\model\User;
use app\model\Like;
use app\model\UserFollow;
use app\config\AppConstants;
use support\Log;
use Throwable;

class JophielSyncProcess
{
    private array $jophielApiConfig;
    private int $syncInterval = 86400; // 24 horas (24 * 60 * 60)
    private int $initialDelay = 10;    // 10 segundos de retardo inicial
    private array $dataTypesToSync = ['samples', 'users', 'likes', 'follows'];

    /**
     * Se ejecuta cuando el proceso arranca.
     */
    public function onWorkerStart(Worker $worker)
    {
        // Inicializar la conexión a la base de datos para este proceso.
        \app\bootstrap\Database::start(null);

        $this->jophielApiConfig = config('jophiel.api');

        // Ejecutar la sincronización una vez al inicio (tras un breve retardo).
        Timer::add($this->initialDelay, [$this, 'runFullSyncCycle'], [], false);

        // Configurar la sincronización periódica.
        Timer::add($this->syncInterval, [$this, 'runFullSyncCycle']);
    }

    /**
     * Orquesta el ciclo de sincronización completo para todos los tipos de datos.
     */
    public function runFullSyncCycle()
    {
        Log::channel('master')->info('[JophielSync] ==> Iniciando ciclo de sincronización completa...');
        foreach ($this->dataTypesToSync as $type) {
            $this->syncDataType($type);
        }
        Log::channel('master')->info('[JophielSync] ==> Ciclo de sincronización finalizado.');
    }

    /**
     * Ejecuta la lógica de sincronización para un tipo de dato específico.
     *
     * @param string $type El tipo de dato a sincronizar (e.g., 'samples', 'users').
     */
    private function syncDataType(string $type)
    {
        Log::channel('master')->info("[JophielSync] Iniciando sincronización para: '{$type}'.");

        try {
            $sword_checksum = $this->calculateSwordChecksum($type);
            $jophiel_checksum = $this->fetchJophielChecksum($type);

            Log::channel('master')->info("[JophielSync] Checksum de Sword para '{$type}': {$sword_checksum}");
            Log::channel('master')->info("[JophielSync] Checksum de Jophiel para '{$type}': {$jophiel_checksum}");

            if ($sword_checksum === $jophiel_checksum) {
                Log::channel('master')->info("[JophielSync] ¡ÉXITO! Los datos para '{$type}' ya están sincronizados.");
                return;
            }

            Log::channel('master')->warning("[JophielSync] Los checksums para '{$type}' no coinciden. Se procederá a una reconciliación completa.");
            $this->performFullReconciliation($type);
        } catch (Throwable $e) {
            Log::channel('master')->critical("[JophielSync] ¡FALLO CRÍTICO! Ocurrió un error sincronizando '{$type}'.", [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
            ]);
        }
    }

    /**
     * Realiza una comparación completa de IDs/relaciones y despacha los eventos necesarios.
     *
     * @param string $type
     */
    private function performFullReconciliation(string $type): void
    {
        $sword_data = $this->getSwordData($type);
        Log::channel('master')->info('[JophielSync] Se encontraron ' . count($sword_data) . " registros para '{$type}' en Sword.");

        $jophiel_data = $this->fetchJophielData($type);
        Log::channel('master')->info('[JophielSync] Se encontraron ' . count($jophiel_data) . " registros para '{$type}' en Jophiel.");

        $items_to_add = array_diff($sword_data, $jophiel_data);
        $items_to_remove = array_diff($jophiel_data, $sword_data);
        $total_ops = count($items_to_add) + count($items_to_remove);

        if ($total_ops === 0) {
            Log::channel('master')->info("[JophielSync] Reconciliación para '{$type}' finalizada. No se encontraron diferencias tras el análisis detallado.");
            return;
        }

        Log::channel('master')->info("[JophielSync] Reconciliación para '{$type}': " . count($items_to_add) . " para añadir, " . count($items_to_remove) . " para eliminar.");

        // Despachar eventos de eliminación
        foreach ($items_to_remove as $item) {
            $this->dispatchEvent($type, 'remove', $item);
        }

        // Despachar eventos de adición
        foreach ($items_to_add as $item) {
            $this->dispatchEvent($type, 'add', $item);
        }

        Log::channel('master')->info("[JophielSync] Reconciliación completa para '{$type}' finalizada con éxito.");
    }

    /**
     * Despacha el evento correcto a Jophiel basado en el tipo y la acción.
     *
     * @param string $type
     * @param string $action 'add' or 'remove'
     * @param mixed $item The ID or relation string
     */
    private function dispatchEvent(string $type, string $action, $item): void
    {
        $is_add = ($action === 'add');

        switch ($type) {
            case 'samples':
                if ($is_add) {
                    $content = Content::find($item);
                    if ($content) {
                        $event = \app\events\SampleCreatedEvent::fromContent($content);
                        Log::channel('master')->info("[JophielSync] Despachando '{$event->getName()}' para el ID: {$content->id}");
                        jophielEvento($event->getName(), $event->toPayload());
                    }
                } else {
                    Log::channel('master')->info("[JophielSync] Despachando 'sample.lifecycle.deleted' para el ID: {$item}");
                    jophielEvento('sample.lifecycle.deleted', ['sample_id' => (int)$item]);
                }
                break;

            case 'users':
                // Nota: Por ahora, solo se sincroniza la creación/eliminación de usuarios.
                // La actualización (ej. cambio de 'username') requeriría un evento 'user.lifecycle.updated'.
                $event = $is_add ? 'user.lifecycle.created' : 'user.lifecycle.deleted';
                Log::channel('master')->info("[JophielSync] Despachando '{$event}' para el User ID: {$item}");
                jophielEvento($event, ['user_id' => (int)$item]);
                break;

            case 'likes':
                list($user_id, $sample_id) = explode('-', $item);
                $event = $is_add ? 'user.interaction.like' : 'user.interaction.unlike';
                Log::channel('master')->info("[JophielSync] Despachando '{$event}' para User:{$user_id}, Sample:{$sample_id}");
                jophielEvento($event, ['user_id' => (int)$user_id, 'sample_id' => (int)$sample_id]);
                break;

            case 'follows':
                list($user_id, $followed_id) = explode('-', $item);
                $event = $is_add ? 'user.interaction.follow' : 'user.interaction.unfollow';
                Log::channel('master')->info("[JophielSync] Despachando '{$event}' para User:{$user_id}, Followed:{$followed_id}");
                jophielEvento($event, [
                    'user_id'          => (int)$user_id,
                    $is_add ? 'followed_user_id' : 'unfollowed_user_id' => (int)$followed_id
                ]);
                break;
        }
    }

    /**
     * Calcula el checksum para un tipo de dato específico desde la base de datos de Sword.
     */
    private function calculateSwordChecksum(string $type): string
    {
        $data = $this->getSwordData($type);
        sort($data);
        return hash('sha256', implode(',', $data));
    }

    /**
     * Obtiene los datos crudos (IDs o relaciones) desde la base de datos local.
     */
    private function getSwordData(string $type): array
    {
        switch ($type) {
            case 'samples':
                return Content::where('type', AppConstants::CONTENT_TYPE_AUDIO_SAMPLE)
                    ->where('status', AppConstants::STATUS_PUBLISHED)
                    ->pluck('id')
                    ->toArray();
            case 'users':
                return User::pluck('id')->toArray();
            case 'likes':
                return Like::join('contents', 'likes.content_id', '=', 'contents.id')
                    ->where('contents.type', AppConstants::CONTENT_TYPE_AUDIO_SAMPLE)
                    ->select('likes.user_id', 'likes.content_id')
                    ->get()
                    ->map(fn($l) => "{$l->user_id}-{$l->content_id}")
                    ->toArray();
            case 'follows':
                return UserFollow::select('user_id', 'followed_user_id')
                    ->get()
                    ->map(fn($f) => "{$f->user_id}-{$f->followed_user_id}")
                    ->toArray();
            default:
                return [];
        }
    }

    /**
     * Construye la URL del endpoint de Jophiel basado en el tipo de dato.
     */
    private function getJophielEndpoint(string $type, bool $forChecksum): string
    {
        $base_url = rtrim($this->jophielApiConfig['base_url'] ?? '', '/');
        $suffix = $forChecksum ? '/checksum' : '/ids'; // Asumimos que la API de Jophiel sigue este patrón.

        // Mapeo de tipos a rutas de API en Jophiel
        $path_map = [
            'samples' => '/v1/samples',
            'users'   => '/v1/users',
            'likes'   => '/v1/interactions/likes',
            'follows' => '/v1/interactions/follows',
        ];

        if (!isset($path_map[$type])) {
            throw new \InvalidArgumentException("Tipo de dato de Jophiel no válido: {$type}");
        }

        return $base_url . $path_map[$type] . $suffix;
    }

    /**
     * Obtiene el checksum desde la API de Jophiel.
     */
    private function fetchJophielChecksum(string $type): ?string
    {
        $url = $this->getJophielEndpoint($type, true);
        $response = $this->makeJophielRequest($url);
        return $response['data']['checksum'] ?? null;
    }

    /**
     * Obtiene los datos (IDs o relaciones) desde la API de Jophiel.
     */
    private function fetchJophielData(string $type): array
    {
        $url = $this->getJophielEndpoint($type, false);
        $response = $this->makeJophielRequest($url);

        // La API de Jophiel debe devolver los datos en una clave que coincida con el tipo.
        // ej: { "data": { "samples": [...] } }, { "data": { "users": [...] } }, etc.
        return $response['data'][$type] ?? [];
    }

    /**
     * Realiza la petición HTTP a la API de Jophiel.
     */
    private function makeJophielRequest(string $url): array
    {
        Log::channel('master')->debug("[JophielSync] Petición síncrona GET a: $url");

        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'timeout'       => $this->jophielApiConfig['timeout'] ?? 10,
                'header'        => "Accept: application/json\r\n",
                'ignore_errors' => true
            ],
        ]);

        $body = @file_get_contents($url, false, $context);

        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('#HTTP/\d+\.\d+\s+(\d{3})#', $http_response_header[0], $matches)) {
            $status_code = (int)$matches[1];
        }

        if ($body === false || $status_code !== 200) {
            throw new \RuntimeException("La API de Jophiel devolvió el código de estado: {$status_code}. URL: {$url}. Body: " . ($body ?: 'N/A'));
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Fallo al decodificar la respuesta JSON de Jophiel. Error: " . json_last_error_msg());
        }

        return $decoded;
    }

    public function runSync(): void
    {
        // Alias para compatibilidad con comandos CLI.
        // Simplemente orquesta un ciclo completo de sincronización.
        $this->runFullSyncCycle();
    }
}
