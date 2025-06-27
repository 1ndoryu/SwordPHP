<?php

namespace Tests\Feature;

use app\Action\CreateContentAction;
use app\model\Role;
use app\model\User;
use app\services\CasielService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Mockery;
use support\Request;
use Tests\TestCase;

// Hook para limpiar la base de datos antes de CADA test
beforeEach(function () {
    // Limpiar tablas para un estado limpio y consistente
    Capsule::schema()->disableForeignKeyConstraints();
    Capsule::table('contents')->truncate();
    Capsule::table('likes')->truncate();
    Capsule::table('comments')->truncate();
    Capsule::table('users')->truncate();
    Capsule::table('roles')->truncate();
    Capsule::schema()->enableForeignKeyConstraints();

    // Crear roles base necesarios para las pruebas
    Role::create(['name' => 'user', 'permissions' => ['content.create']]);
});

test('creating an audio_sample content triggers casiel service notification - integration test', function () {
    // --- 1. Setup: Crear un usuario y un mock del Request ---
    // La acción necesita un usuario con un rol para funcionar.
    $userRole = Role::where('name', 'user')->first();
    $user = User::create([
        'username' => 'testuser_integration',
        'email' => 'test_int@test.com',
        'password' => 'password',
        'role_id' => $userRole->id
    ]);

    // Creamos un mock del objeto Request para simular los datos de entrada.
    $postData = [
        'type' => 'audio_sample',
        'status' => 'published',
        'content_data' => [
            'title' => 'Test Audio Sample Event',
            'media_id' => 123
        ]
    ];
    $requestMock = Mockery::mock(Request::class);
    $requestMock->shouldReceive('post')->andReturn($postData); // Simula el método post()
    $requestMock->user = $user; // Adjuntamos el usuario al mock del request.

    // --- 2. Mocking: Preparar el mock de CasielService ---
    $casielMock = Mockery::mock(CasielService::class);
    // Esperamos que se llame con un ID de contenido (int) y el media_id 123.
    $casielMock->shouldReceive('notifyNewAudio')->once()->with(Mockery::type('integer'), 123);
    CasielService::setInstanceForTesting($casielMock);

    // --- 3. Acción: Instanciar y llamar a la clase de acción directamente ---
    $action = new CreateContentAction();
    $response = $action($requestMock); // Llamamos a la acción con nuestro request simulado

    // --- 4. Aserción: Verificar la respuesta de la acción ---
    expect($response->getStatusCode())->toBe(201);
    $body = json_decode($response->rawBody(), true);
    expect($body['success'])->toBeTrue();
    expect($body['data']['type'])->toBe('audio_sample');

    // La expectativa de Mockery se verificará automáticamente en el `tearDown` de TestCase.
});

// Reseteamos la instancia del mock después del test para no afectar a otros
afterEach(function () {
    CasielService::setInstanceForTesting(null);
});