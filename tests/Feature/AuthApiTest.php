<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users for each test
    $this->adminUser = User::factory()->create([
        'email' => 'admin@test.com',
        'auth' => 'Admin',
        'status' => 'Active',
    ]);
    
    $this->customerUser = User::factory()->create([
        'email' => 'customer@test.com',
        'auth' => 'Customer',
        'status' => 'Active',
    ]);
    
    $this->inactiveUser = User::factory()->create([
        'email' => 'inactive@test.com',
        'auth' => 'Customer',
        'status' => 'Inactive',
    ]);
});

describe('Authentication API', function () {
    
    test('login with valid credentials returns token', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id', 'name', 'email', 'role', 'point', 'foto_url', 'no_telp', 'alamat'
                        ],
                        'token',
                        'token_type'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'data' => [
                        'user' => [
                            'email' => 'admin@test.com',
                            'role' => 'Admin'
                        ],
                        'token_type' => 'Bearer'
                    ]
                ]);

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->adminUser->id,
            'name' => 'test-device',
        ]);
    });

    test('login with invalid credentials returns error', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('success', false);
    });

    test('login with inactive user returns error', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('success', false);
    });

    test('login validation errors for invalid input', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => 'short',
            'device_name' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data yang dikirim tidak valid',
                ])
                ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    });

    test('register with valid data creates user and returns token', function () {
        $userData = [
            'name' => 'Test Customer',
            'email' => 'newcustomer@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'no_telp' => '081234567890',
            'alamat' => 'Test Address',
            'device_name' => 'test-device',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role', 'point', 'no_telp', 'alamat'],
                        'token',
                        'token_type'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => [
                        'user' => [
                            'email' => 'newcustomer@test.com',
                            'role' => 'Customer',
                            'point' => 0
                        ]
                    ]
                ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@test.com',
            'auth' => 'Customer',
            'status' => 'Active',
        ]);
    });

    test('register validation errors for invalid data', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'admin@test.com', // duplicate
            'password' => 'short',
            'password_confirmation' => 'different',
            'no_telp' => '',
            'alamat' => '',
            'device_name' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data registrasi tidak valid',
                ])
                ->assertJsonValidationErrors([
                    'name', 'email', 'password', 'no_telp', 'alamat', 'device_name'
                ]);
    });

    test('logout deletes current token', function () {
        $token = $this->adminUser->createToken('test-device')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout berhasil',
                ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->adminUser->id,
            'name' => 'test-device',
        ]);
    });

    test('logout all deletes all user tokens', function () {
        $this->adminUser->createToken('device-1');
        $this->adminUser->createToken('device-2');
        $token = $this->adminUser->createToken('device-3')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout-all');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout dari semua device berhasil',
                ]);

        // Verify all tokens were deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->adminUser->id,
        ]);
    });

    test('get profile returns authenticated user data', function () {
        $token = $this->adminUser->createToken('test-device')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id', 'name', 'email', 'role', 'point', 'foto_url', 'no_telp', 'alamat'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'email' => 'admin@test.com',
                        'role' => 'Admin'
                    ]
                ]);
    });

    test('check email returns taken for existing email', function () {
        $response = $this->postJson('/api/v1/auth/check-email', [
            'email' => 'admin@test.com',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'available' => false,
                        'message' => 'Email sudah terdaftar',
                    ]
                ]);
    });

    test('check email returns available for new email', function () {
        $response = $this->postJson('/api/v1/auth/check-email', [
            'email' => 'newemail@test.com',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'available' => true,
                        'message' => 'Email tersedia',
                    ]
                ]);
    });

    test('protected endpoints require authentication', function () {
        $endpoints = [
            ['method' => 'post', 'url' => '/api/v1/auth/logout'],
            ['method' => 'post', 'url' => '/api/v1/auth/logout-all'],
            ['method' => 'get', 'url' => '/api/v1/auth/profile'],
            ['method' => 'put', 'url' => '/api/v1/auth/update-password'],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->{$endpoint['method'] . 'Json'}($endpoint['url']);
            $response->assertStatus(401);
        }
    });

});