<?php

use App\Models\Post;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

function validPostPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'My First Post',
        'status' => 'draft',
        'content' => 'Hello world',
    ], $overrides);
}

function createPostForUser(User $user, array $overrides = []): Post
{
    return $user->posts()->create(validPostPayload($overrides));
}

test('Posts endpoints require authentication', function (string $method, string $uri, ?array $payload) {
    $post = createPostForUser(User::factory()->create());

    $uri = str_replace('{post}', (string) $post->id, $uri);

    $response = match ($method) {
        'GET' => $this->getJson($uri),
        'POST' => $this->postJson($uri, $payload ?? []),
        'PUT' => $this->putJson($uri, $payload ?? []),
        'DELETE' => $this->deleteJson($uri),
    };

    $response
        ->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated',
            'data' => null,
            'errors' => null,
        ]);
})->with([
            'index' => ['GET', '/api/v1/posts', null],
            'store' => ['POST', '/api/v1/posts', ['title' => 'X']],
            'show' => ['GET', '/api/v1/posts/{post}', null],
            'update' => ['PUT', '/api/v1/posts/{post}', ['title' => 'Updated']],
            'destroy' => ['DELETE', '/api/v1/posts/{post}', null],
        ]);

test('Authenticated user can list posts (paginated)', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    foreach (range(1, 6) as $i) {
        createPostForUser($user, ['title' => "Post {$i}"]);
    }

    $this->getJson('/api/v1/posts')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'errors' => null,
        ])
        ->assertJsonStructure([
            'data' => ['data', 'links', 'meta'],
        ])
        ->assertJsonPath('data.meta.per_page', 5)
        ->assertJsonCount(5, 'data.data');
});

test('Authenticated user can create a post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = validPostPayload([
        'title' => 'Hello Laravel',
        'status' => 'published',
    ]);

    $this->postJson('/api/v1/posts', $payload)
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Post created successfully',
            'errors' => null,
        ])
        ->assertJsonPath('data.title', 'Hello Laravel')
        ->assertJsonPath('data.slug', 'hello-laravel');

    $this->assertDatabaseHas('posts', [
        'title' => 'Hello Laravel',
        'status' => 'published',
        'user_id' => $user->id,
    ]);
});

test('Create post validates input', function (array $payload, array $errorKeys) {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/posts', $payload)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors',
        ])
        ->assertJsonStructure([
            'errors' => $errorKeys,
        ]);
})->with([
            'missing all fields' => [
                [],
                ['title', 'status', 'content'],
            ],
            'invalid status' => [
                validPostPayload(['status' => 'invalid']),
                ['status'],
            ],
            'title too long' => [
                validPostPayload(['title' => str_repeat('a', 256)]),
                ['title'],
            ],
        ]);

test('Create post cannot assign another user_id', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/posts', validPostPayload([
        'user_id' => $otherUser->id,
        'title' => 'Ownership Test',
    ]))
        ->assertCreated();

    $this->assertDatabaseHas('posts', [
        'title' => 'Ownership Test',
        'user_id' => $user->id,
    ]);
});

test('Authenticated user can view a post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user, ['title' => 'View Me']);

    $this->getJson("/api/v1/posts/{$post->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'errors' => null,
        ])
        ->assertJsonPath('data.id', $post->id)
        ->assertJsonPath('data.title', 'View Me')
        ->assertJsonPath('data.slug', 'view-me');
});

test('Viewing a missing post returns a not found response', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/posts/999999')
        ->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
            'errors' => null,
        ]);
});

test('Post owner can update a post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user, ['title' => 'Old Title']);

    $this->putJson("/api/v1/posts/{$post->id}", [
        'title' => 'New Title',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Post updated successfully',
            'errors' => null,
        ])
        ->assertJsonPath('data.title', 'New Title')
        ->assertJsonPath('data.slug', 'new-title');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'New Title',
    ]);
});

test('Update validates input', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $post = createPostForUser($user);

    $this->putJson("/api/v1/posts/{$post->id}", ['status' => 'invalid'])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
        ])
        ->assertJsonStructure([
            'errors' => ['status'],
        ]);
});

test('Non-owner cannot update a post', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();

    $post = createPostForUser($owner);

    Sanctum::actingAs($nonOwner);

    $this->putJson("/api/v1/posts/{$post->id}", ['title' => 'Hacked'])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden',
            'data' => null,
            'errors' => null,
        ]);
});

test('Post owner can delete a post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user);

    $this->deleteJson("/api/v1/posts/{$post->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

test('Non-owner cannot delete a post', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();

    $post = createPostForUser($owner);

    Sanctum::actingAs($nonOwner);

    $this->deleteJson("/api/v1/posts/{$post->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden',
            'data' => null,
            'errors' => null,
        ]);
});

test('Deleting a missing post returns a not found response', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/v1/posts/999999')
        ->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
            'errors' => null,
        ]);
});
