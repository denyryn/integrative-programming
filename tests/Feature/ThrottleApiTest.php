<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('Posts index is throttled after 5 requests per minute for an authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    foreach (range(1, 5) as $i) {
        $this->getJson('/api/v1/posts')->assertOk();
    }

    $this->getJson('/api/v1/posts')
        ->assertStatus(429)
        ->assertJson([
            'success' => false,
            'message' => 'Too Many Attempts.',
            'data' => null,
            'errors' => null,
        ]);
});

test('Throttle resets after one minute', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    foreach (range(1, 5) as $i) {
        $this->getJson('/api/v1/posts')->assertOk();
    }

    $this->getJson('/api/v1/posts')->assertStatus(429);

    $this->travel(1)->minutes();

    $this->getJson('/api/v1/posts')->assertOk();
});

test('Throttle limit is per authenticated user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Sanctum::actingAs($userA);

    foreach (range(1, 5) as $i) {
        $this->getJson('/api/v1/posts')->assertOk();
    }

    $this->getJson('/api/v1/posts')->assertStatus(429);

    Sanctum::actingAs($userB);

    foreach (range(1, 5) as $i) {
        $this->getJson('/api/v1/posts')->assertOk();
    }
});
