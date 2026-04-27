<?php

use App\Models\Comment;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

function validCommentPayload(int $postId, array $overrides = []): array
{
    return array_merge([
        'comment' => 'Nice post!',
        'post_id' => $postId,
    ], $overrides);
}

function createCommentForUser(User $user, int $postId, array $overrides = []): Comment
{
    return $user->comments()->create(validCommentPayload($postId, $overrides));
}

test('Comments endpoints require authentication', function (string $method, string $uri, ?array $payload) {
    $user = User::factory()->create();
    $post = createPostForUser($user);
    $comment = createCommentForUser($user, $post->id);

    $uri = str_replace('{comment}', (string) $comment->id, $uri);

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
            'index' => ['GET', '/api/v1/comments', null],
            'store' => ['POST', '/api/v1/comments', ['comment' => 'X']],
            'show' => ['GET', '/api/v1/comments/{comment}', null],
            'update' => ['PUT', '/api/v1/comments/{comment}', ['comment' => 'Updated']],
            'destroy' => ['DELETE', '/api/v1/comments/{comment}', null],
        ]);

test('Authenticated user cannot list comments due to policy', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/comments')
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden',
            'data' => null,
            'errors' => null,
        ]);
});

test('Authenticated user can create a comment on an existing post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user);
    $payload = validCommentPayload($post->id, ['comment' => 'First!']);

    $this->postJson('/api/v1/comments', $payload)
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Comment created successfully',
            'errors' => null,
        ])
        ->assertJsonPath('data.content', 'First!');

    $this->assertDatabaseHas('comments', [
        'comment' => 'First!',
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

test('Create comment validates input', function (array $payload, array $errorKeys) {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/comments', $payload)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
        ])
        ->assertJsonStructure([
            'errors' => $errorKeys,
        ]);
})->with([
            'missing all fields' => [
                [],
                ['comment', 'post_id'],
            ],
            'missing post_id' => [
                ['comment' => 'Nice'],
                ['post_id'],
            ],
            'missing comment' => [
                ['post_id' => 123],
                ['comment'],
            ],
            'non-existent post_id' => [
                ['comment' => 'Nice', 'post_id' => 999999],
                ['post_id'],
            ],
            'comment too long' => [
                ['comment' => str_repeat('a', 256), 'post_id' => 1],
                ['comment', 'post_id'],
            ],
        ]);

test('Create comment cannot assign another user_id', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Sanctum::actingAs($user);

    $post = createPostForUser($user);

    $this->postJson('/api/v1/comments', validCommentPayload($post->id, [
        'comment' => 'Ownership Test',
        'user_id' => $otherUser->id,
    ]))
        ->assertCreated();

    $this->assertDatabaseHas('comments', [
        'comment' => 'Ownership Test',
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

test('Authenticated user can view a comment', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $post = createPostForUser($owner);

    $comment = createCommentForUser($owner, $post->id, ['comment' => 'View me']);

    Sanctum::actingAs($viewer);

    $this->getJson("/api/v1/comments/{$comment->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Comment retrieved successfully',
            'errors' => null,
        ])
        ->assertJsonPath('data.id', $comment->id)
        ->assertJsonPath('data.content', 'View me');
});

test('Viewing a missing comment returns a not found response', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/comments/999999')
        ->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
            'errors' => null,
        ]);
});

test('Comment owner can update a comment', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user);
    $comment = createCommentForUser($user, $post->id, ['comment' => 'Old']);

    $this->putJson("/api/v1/comments/{$comment->id}", ['comment' => 'New'])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Comment updated successfully',
            'errors' => null,
        ])
        ->assertJsonPath('data.content', 'New');

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'comment' => 'New',
    ]);
});

test('Update comment validates input', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user);
    $comment = createCommentForUser($user, $post->id);

    $this->putJson("/api/v1/comments/{$comment->id}", ['comment' => str_repeat('a', 256)])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
        ])
        ->assertJsonStructure([
            'errors' => ['comment'],
        ]);
});

test('Non-owner cannot update a comment', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();

    $post = createPostForUser($owner);
    $comment = createCommentForUser($owner, $post->id);

    Sanctum::actingAs($nonOwner);

    $this->putJson("/api/v1/comments/{$comment->id}", ['comment' => 'Hacked'])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden',
            'data' => null,
            'errors' => null,
        ]);
});

test('Comment owner can delete a comment', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $post = createPostForUser($user);
    $comment = createCommentForUser($user, $post->id);

    $this->deleteJson("/api/v1/comments/{$comment->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

test('Non-owner cannot delete a comment', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();

    $post = createPostForUser($owner);
    $comment = createCommentForUser($owner, $post->id);

    Sanctum::actingAs($nonOwner);

    $this->deleteJson("/api/v1/comments/{$comment->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden',
            'data' => null,
            'errors' => null,
        ]);
});

test('Deleting a missing comment returns a not found response', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/v1/comments/999999')
        ->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
            'errors' => null,
        ]);
});
