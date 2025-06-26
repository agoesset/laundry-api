<?php

use App\Models\User;
use App\Models\Price;
use App\Models\LaundrySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users
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
    
    $this->karyawanUser = User::factory()->create([
        'email' => 'karyawan@test.com',
        'auth' => 'Karyawan',
        'status' => 'Active',
    ]);

    // Create test prices
    $this->activePrice = Price::factory()->create([
        'user_id' => $this->adminUser->id,
        'jenis' => 'Cuci Kering',
        'kg' => '1 kg',
        'harga' => 5000,
        'hari' => 1,
        'status' => 'Active',
    ]);

    $this->inactivePrice = Price::factory()->create([
        'user_id' => $this->adminUser->id,
        'jenis' => 'Cuci Premium',
        'kg' => '1 kg',
        'harga' => 15000,
        'hari' => 2,
        'status' => 'Inactive',
    ]);

    // Create admin token
    $this->adminToken = $this->adminUser->createToken('test-device')->plainTextToken;
    $this->customerToken = $this->customerUser->createToken('test-device')->plainTextToken;
});

describe('Price API', function () {
    
    test('get prices returns active prices for customer', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->getJson('/api/v1/prices');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id', 'jenis', 'kg', 'harga', 'hari', 'status', 'user'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Data harga berhasil diambil',
                ]);

        // Should only show active prices
        $data = $response->json('data');
        expect(collect($data)->every(fn($price) => $price['status'] === 'Active'))->toBeTrue();
    });

    test('admin can see all prices including inactive', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/prices?show_all=1');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $statuses = collect($data)->pluck('status')->unique();
        expect($statuses->contains('Active'))->toBeTrue();
        expect($statuses->contains('Inactive'))->toBeTrue();
    });

    test('get price detail by id', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/prices/{$this->activePrice->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id', 'jenis', 'kg', 'harga', 'hari', 'status', 'user', 'estimasi_selesai'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->activePrice->id,
                        'jenis' => 'Cuci Kering',
                    ]
                ]);
    });

    test('admin can create new price', function () {
        $priceData = [
            'jenis' => 'Cuci Express',
            'kg' => '1 kg',
            'harga' => 8000,
            'hari' => 1,
            'status' => 'Active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/prices', $priceData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id', 'jenis', 'kg', 'harga', 'hari', 'status', 'user'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Harga berhasil ditambahkan',
                    'data' => [
                        'jenis' => 'Cuci Express',
                        'harga' => 8000,
                    ]
                ]);

        // Verify database
        $this->assertDatabaseHas('prices', [
            'jenis' => 'Cuci Express',
            'harga' => 8000,
            'user_id' => $this->adminUser->id,
        ]);
    });

    test('customer cannot create price', function () {
        $priceData = [
            'jenis' => 'Cuci Test',
            'kg' => '1 kg',
            'harga' => 5000,
            'hari' => 1,
            'status' => 'Active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->postJson('/api/v1/prices', $priceData);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Hanya admin yang bisa menambah harga layanan',
                ]);
    });

    test('create price validation errors', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/prices', [
            'jenis' => '', // required
            'kg' => '',   // required
            'harga' => 500, // below minimum
            'hari' => 0,   // below minimum
            'status' => 'Invalid', // invalid enum
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data harga layanan tidak valid',
                ])
                ->assertJsonValidationErrors(['jenis', 'kg', 'harga', 'hari', 'status']);
    });

    test('cannot create duplicate active service type', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/prices', [
            'jenis' => 'Cuci Kering', // duplicate
            'kg' => '1 kg',
            'harga' => 6000,
            'hari' => 1,
            'status' => 'Active',
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('errors.jenis.0', 'Jenis layanan "Cuci Kering" sudah ada dan aktif');
    });

    test('admin can update price', function () {
        $updateData = [
            'harga' => 6000,
            'hari' => 2,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/prices/{$this->activePrice->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Harga berhasil diupdate',
                    'data' => [
                        'harga' => 6000,
                        'hari' => 2,
                    ]
                ]);

        // Verify database
        $this->assertDatabaseHas('prices', [
            'id' => $this->activePrice->id,
            'harga' => 6000,
            'hari' => 2,
        ]);
    });

    test('customer cannot update price', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->putJson("/api/v1/prices/{$this->activePrice->id}", [
            'harga' => 6000,
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Hanya admin yang bisa mengubah harga',
                ]);
    });

    test('admin can delete unused price', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/prices/{$this->activePrice->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Harga berhasil dihapus',
                ]);

        // Verify soft delete or actual delete
        $this->assertDatabaseMissing('prices', [
            'id' => $this->activePrice->id,
        ]);
    });

    test('customer cannot delete price', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->deleteJson("/api/v1/prices/{$this->activePrice->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Hanya admin yang bisa menghapus harga',
                ]);
    });

    test('get jenis list returns unique service types', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->getJson('/api/v1/prices/jenis-list');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'List jenis layanan berhasil diambil',
                ]);

        $data = $response->json('data');
        expect($data)->toBeArray();
        expect($data)->toContain('Cuci Kering');
    });

    test('price endpoints require authentication', function () {
        $endpoints = [
            ['method' => 'get', 'url' => '/api/v1/prices'],
            ['method' => 'post', 'url' => '/api/v1/prices'],
            ['method' => 'get', 'url' => '/api/v1/prices/1'],
            ['method' => 'put', 'url' => '/api/v1/prices/1'],
            ['method' => 'delete', 'url' => '/api/v1/prices/1'],
            ['method' => 'get', 'url' => '/api/v1/prices/jenis-list'],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->{$endpoint['method'] . 'Json'}($endpoint['url']);
            $response->assertStatus(401);
        }
    });

    test('price not found returns 404', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/prices/999999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Harga tidak ditemukan',
                ]);
    });

});