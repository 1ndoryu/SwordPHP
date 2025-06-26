<?php

// Hook para resetear la base de datos antes de CADA test.
// Esto asegura que cada prueba se ejecuta en un ambiente limpio y aislado.
beforeEach(function () {
    // Reset and Install DB via API endpoints
    $resetResponse = $this->http->post('/system/reset');
    expect($resetResponse->getStatusCode())->toBe(200);

    $installResponse = $this->http->post('/system/install');
    expect($installResponse->getStatusCode())->toBe(200);
});

test('full api e2e flow', function () {
    // --- FASE 1: Registro y Autenticación ---
    $rand = time();
    $adminCreds = ['username' => "admin_$rand", 'email' => "admin_$rand@test.com", 'password' => 'password123'];
    $userCreds = ['username' => "user_$rand", 'email' => "user_$rand@test.com", 'password' => 'password123'];

    // Registrar usuarios
    $regAdmin = $this->postJson('/auth/register', $adminCreds);
    expect($regAdmin->getStatusCode())->toBe(200);

    $regUser = $this->postJson('/auth/register', $userCreds);
    expect($regUser->getStatusCode())->toBe(200);

    // Login y obtención de tokens
    $loginAdminResponse = $this->postJson('/auth/login', ['identifier' => $adminCreds['email'], 'password' => 'password123']);
    expect($loginAdminResponse->getStatusCode())->toBe(200);
    $adminBody = json_decode($loginAdminResponse->getBody()->getContents(), true);
    $adminToken = $adminBody['data']['access_token'];
    expect($adminToken)->toBeString()->not->toBeEmpty();

    $loginUserResponse = $this->postJson('/auth/login', ['identifier' => $userCreds['email'], 'password' => 'password123']);
    expect($loginUserResponse->getStatusCode())->toBe(200);
    $userBody = json_decode($loginUserResponse->getBody()->getContents(), true);
    $userToken = $userBody['data']['access_token'];
    expect($userToken)->toBeString()->not->toBeEmpty();
    
    // --- FASE 2: Perfil de Usuario ---
    $profileResponse = $this->getJson('/user/profile', $userToken);
    expect($profileResponse->getStatusCode())->toBe(200);
    $profileData = json_decode($profileResponse->getBody()->getContents(), true);
    expect($profileData['user']['email'])->toBe($userCreds['email']);

    // --- FASE 3: CRUD de Contenido y Social ---
    // Crear contenido
    $createContentResponse = $this->postJson('/contents', [
        'status' => 'published',
        'content_data' => ['title' => 'Post de Prueba Automático', 'body' => 'Cuerpo del post.']
    ], $userToken);
    expect($createContentResponse->getStatusCode())->toBe(201);
    $contentData = json_decode($createContentResponse->getBody()->getContents(), true);
    $contentId = $contentData['data']['id'];
    $contentSlug = $contentData['data']['slug'];
    expect($contentId)->toBeInt();

    // Dar like
    $likeResponse = $this->postJson("/contents/{$contentId}/like", [], $userToken);
    expect($likeResponse->getStatusCode())->toBe(200);

    // Comentar
    $commentResponse = $this->postJson("/comments/{$contentId}", ['body' => 'Test comment'], $userToken);
    expect($commentResponse->getStatusCode())->toBe(201);
    $commentData = json_decode($commentResponse->getBody()->getContents(), true);
    $commentId = $commentData['data']['id'];
    expect($commentId)->toBeInt();

    // --- FASE 4: Gestión de Administrador ---
    // Subir archivo
    $uploadResponse = $this->http->post('/media', [
        'headers' => ['Authorization' => 'Bearer ' . $userToken],
        'multipart' => [['name' => 'file', 'contents' => 'dummy content', 'filename' => 'test.txt']]
    ]);
    expect($uploadResponse->getStatusCode())->toBe(201);
    $uploadData = json_decode($uploadResponse->getBody()->getContents(), true);
    $mediaId = $uploadData['data']['id'];
    expect($mediaId)->toBeInt();

    // Crear rol "editor"
    $roleResponse = $this->postJson('/admin/roles', [
        'name' => 'editor', 'description' => 'Test Editor Role', 'permissions' => ['content.update']
    ], $adminToken);
    expect($roleResponse->getStatusCode())->toBe(201);
    $roleData = json_decode($roleResponse->getBody()->getContents(), true);
    $editorRoleId = $roleData['data']['id'];

    // --- FASE 5: Limpieza (Cleanup) ---
    // User elimina su propio comentario
    $this->deleteJson("/comments/{$commentId}", $userToken)->getStatusCode(204);

    // Admin elimina el archivo subido
    $this->deleteJson("/admin/media/{$mediaId}", $adminToken)->getStatusCode(204);
    
    // User elimina su propio contenido
    $this->deleteJson("/contents/{$contentId}", $userToken)->getStatusCode(204);

    // Admin revierte el rol del usuario para poder eliminar el rol 'editor'
    $userId = 2; // El segundo usuario registrado
    $userRoleId = 2; // El rol 'user' por defecto
    $this->postJson("/admin/users/{$userId}/role", ['role_id' => $userRoleId], $adminToken);

    // Admin elimina el rol creado
    $this->deleteJson("/admin/roles/{$editorRoleId}", $adminToken)->getStatusCode(204);
});