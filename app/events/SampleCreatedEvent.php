<?php

namespace app\events;

use app\model\Content;

/**
 * DTO/Serializer para el evento "sample.lifecycle.created".
 * Encapsula la carga útil con los campos requeridos por Jophiel.
 */
class SampleCreatedEvent
{
    public const NAME = 'sample.lifecycle.created';

    private int $sample_id;
    private int $creator_id;
    private array $metadata;

    public function __construct(int $sample_id, int $creator_id, array $metadata)
    {
        $this->sample_id  = $sample_id;
        $this->creator_id = $creator_id;
        $this->metadata   = $metadata;
    }

    /**
     * Fábrica a partir de un modelo Content.
     */
    public static function fromContent(Content $content): self
    {
        return new self((int) $content->id, (int) $content->user_id, $content->content_data ?? []);
    }

    /**
     * Nombre (routing-key) del evento.
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Cuerpo para el mensaje.
     */
    public function toPayload(): array
    {
        return [
            'sample_id'  => $this->sample_id,
            'creator_id' => $this->creator_id,
            'metadata'   => $this->metadata,
        ];
    }
} 