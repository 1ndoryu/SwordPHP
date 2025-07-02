<?php

use app\events\SampleCreatedEvent;

// Test simple del DTO SampleCreatedEvent

test('sample created event payload is formatted correctly', function () {
    $metadata = ['title' => 'test'];
    $event    = new SampleCreatedEvent(123, 42, $metadata);

    expect($event->getName())->toBe('sample.lifecycle.created');
    expect($event->toPayload())->toMatchArray([
        'sample_id'  => 123,
        'creator_id' => 42,
        'metadata'   => $metadata,
    ]);
}); 