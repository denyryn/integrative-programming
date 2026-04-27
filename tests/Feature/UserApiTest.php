<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('User can register', function () {
    $response = $this->postJson('/auth/register', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ])
        ->assertJsonPath('data.email', 'user@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'user@example.com',
    ]);
});

test('Register requires valid input', function (array $payload, array $errorKeys) {
    $this->postJson('/auth/register', $payload)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
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
        ['name', 'email', 'password'],
    ],
    'invalid email' => [
        [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ],
        ['email'],
    ],
    'password too short' => [
        [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ],
        ['password'],
    ],
    'password confirmation mismatch' => [
        [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ],
        ['password'],
    ],
]);

test('Register rejects duplicate email', function () {
    User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $this->postJson('/auth/register', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ])
        ->assertJsonStructure([
            'errors' => ['email'],
        ]);
});

test('User can login', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logged in successfully',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors',
        ])
        ->assertJsonPath('data.email', 'user@example.com')
        ->assertJsonMissingPath('data.password');
});

test('Login requires valid input', function (array $payload, array $errorKeys) {
    $this->postJson('/auth/login', $payload)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
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
        ['email', 'password'],
    ],
    'invalid email' => [
        ['email' => 'not-an-email', 'password' => 'password'],
        ['email'],
    ],
]);

test('User can logout', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $this->postJson('/auth/logout')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);

    $this->assertGuest('web');
});

test('Logout requires authentication', function () {
    $this->postJson('/auth/logout')
        ->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated',
        ]);
});

test('Wrong credentials return error', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/auth/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
    ]);

    $response
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
            'data' => null,
        ]);
});
