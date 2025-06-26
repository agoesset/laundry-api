<?php

use App\Models\User;
use App\Models\Price;
use App\Models\Transaction;
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

    // Create test price
    $this->price = Price::factory()->create([
        'user_id' => $this->adminUser->id,
        'jenis' => 'Cuci Kering',
        'kg' => '1 kg',
        'harga' => 5000,
        'hari' => 1,
        'status' => 'Active',
    ]);

    // Create laundry settings
    $this->settings = LaundrySetting::create([
        'user_id' => $this->adminUser->id,
        'company_name' => 'Test Laundry',
        'company_address' => 'Test Address',
        'company_phone' => '081234567890',
        'company_email' => 'test@laundry.com',
        'opening_time' => '08:00:00',
        'closing_time' => '20:00:00',
        'working_days' => json_encode([1, 2, 3, 4, 5, 6]),
        'invoice_prefix' => 'LND',
        'invoice_counter' => 1,
        'whatsapp_notification' => false,
        'whatsapp_token' => null,
        'email_notification' => true,
        'telegram_notification' => false,
        'telegram_token' => null,
        'telegram_chat_id' => null,
        'minimum_order' => 10000.00,
        'allow_discount' => true,
        'max_discount_percent' => 20.00,
        'is_active' => true,
    ]);

    // Create test transaction
    $this->transaction = Transaction::factory()->create([
        'customer_id' => $this->customerUser->id,
        'user_id' => $this->adminUser->id,
        'price_id' => $this->price->id,
        'invoice' => 'LND-20250626-0001',
        'berat' => 3.5,
        'total_harga' => 17500,
        'diskon' => 0,
        'harga_akhir' => 17500,
        'catatan' => 'Test transaction',
        'status_order' => 'Process',
        'status_payment' => 'Pending',
    ]);

    // Create tokens
    $this->adminToken = $this->adminUser->createToken('test-device')->plainTextToken;
    $this->customerToken = $this->customerUser->createToken('test-device')->plainTextToken;
    $this->karyawanToken = $this->karyawanUser->createToken('test-device')->plainTextToken;
});

describe('Transaction API', function () {
    
    test('customer can get their own transactions', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->getJson('/api/v1/transactions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id', 'invoice', 'customer', 'user', 'price', 'berat', 
                                'total_harga', 'diskon', 'harga_akhir', 'catatan',
                                'status_order', 'status_payment', 'created_at'
                            ]
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Data transaksi berhasil diambil',
                ]);

        // Should only see own transactions
        $transactions = $response->json('data.data');
        foreach ($transactions as $transaction) {
            expect($transaction['customer']['id'])->toBe($this->customerUser->id);
        }
    });

    test('admin can get all transactions', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/transactions');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data transaksi berhasil diambil',
                ]);
    });

    test('get transaction detail by id', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/transactions/{$this->transaction->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id', 'invoice', 'customer', 'user', 'price', 'berat',
                        'total_harga', 'diskon', 'harga_akhir', 'catatan',
                        'status_order', 'status_payment', 'created_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->transaction->id,
                        'invoice' => 'LND-20250626-0001',
                    ]
                ]);
    });

    test('customer cannot access other customer transactions', function () {
        $otherCustomer = User::factory()->create([
            'auth' => 'Customer',
            'status' => 'Active',
        ]);
        
        $otherTransaction = Transaction::factory()->create([
            'customer_id' => $otherCustomer->id,
            'user_id' => $this->adminUser->id,
            'price_id' => $this->price->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->getJson("/api/v1/transactions/{$otherTransaction->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke transaksi ini',
                ]);
    });

    test('admin can create new transaction', function () {
        $transactionData = [
            'customer_id' => $this->customerUser->id,
            'price_id' => $this->price->id,
            'berat' => 2.5,
            'catatan' => 'New test transaction',
            'diskon' => 10,
            'status_order' => 'Process',
            'status_payment' => 'Pending',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/transactions', $transactionData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id', 'invoice', 'customer', 'user', 'price', 'berat',
                        'total_harga', 'diskon', 'harga_akhir', 'catatan',
                        'status_order', 'status_payment'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Transaksi berhasil dibuat',
                    'data' => [
                        'berat' => 2.5,
                        'diskon' => 10,
                        'status_order' => 'Process',
                    ]
                ]);

        // Verify database
        $this->assertDatabaseHas('transactions', [
            'customer_id' => $this->customerUser->id,
            'price_id' => $this->price->id,
            'berat' => 2.5,
            'diskon' => 10,
        ]);
    });

    test('customer cannot create transaction', function () {
        $transactionData = [
            'customer_id' => $this->customerUser->id,
            'price_id' => $this->price->id,
            'berat' => 2.0,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->postJson('/api/v1/transactions', $transactionData);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk membuat transaksi',
                ]);
    });

    test('create transaction validation errors', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/transactions', [
            'customer_id' => 999999, // non-existent
            'price_id' => 999999,    // non-existent
            'berat' => 0,            // below minimum
            'diskon' => 150,         // above maximum
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data transaksi tidak valid',
                ])
                ->assertJsonValidationErrors(['customer_id', 'price_id', 'berat', 'diskon']);
    });

    test('create transaction with minimum order validation', function () {
        // Total: 1 kg × 5000 = 5000 (below minimum 10000)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/transactions', [
            'customer_id' => $this->customerUser->id,
            'price_id' => $this->price->id,
            'berat' => 1.0, // 1 kg × 5000 = 5000 < 10000 minimum
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('errors.berat.0', 'Minimum order Rp 10.000');
    });

    test('admin can update transaction status', function () {
        $updateData = [
            'status_order' => 'Done',
            'status_payment' => 'Success',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/transactions/{$this->transaction->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Transaksi berhasil diupdate',
                    'data' => [
                        'status_order' => 'Done',
                        'status_payment' => 'Success',
                    ]
                ]);

        // Verify database
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'status_order' => 'Done',
            'status_payment' => 'Success',
        ]);
    });

    test('cannot update transaction status backwards', function () {
        // First update to Done
        $this->transaction->update(['status_order' => 'Done']);

        // Try to update back to Process (should fail)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/transactions/{$this->transaction->id}", [
            'status_order' => 'Process',
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('errors.status_order.0', 'Status order tidak bisa diubah dari Done ke Process');
    });

    test('customer cannot update transaction', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->putJson("/api/v1/transactions/{$this->transaction->id}", [
            'status_order' => 'Done',
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah transaksi',
                ]);
    });

    test('admin can delete transaction', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/transactions/{$this->transaction->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Transaksi berhasil dihapus',
                ]);

        // Verify soft delete or actual delete
        $this->assertDatabaseMissing('transactions', [
            'id' => $this->transaction->id,
        ]);
    });

    test('customer cannot delete transaction', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->deleteJson("/api/v1/transactions/{$this->transaction->id}");

        $response->assertStatus(403);
    });

    test('get transaction summary for admin', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/transactions/summary');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'total_transactions',
                        'total_revenue',
                        'pending_transactions',
                        'completed_transactions',
                    ]
                ])
                ->assertJson([
                    'success' => true,
                ]);
    });

    test('customer cannot access transaction summary', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->getJson('/api/v1/transactions/summary');

        $response->assertStatus(403);
    });

    test('transaction endpoints require authentication', function () {
        $endpoints = [
            ['method' => 'get', 'url' => '/api/v1/transactions'],
            ['method' => 'post', 'url' => '/api/v1/transactions'],
            ['method' => 'get', 'url' => '/api/v1/transactions/1'],
            ['method' => 'put', 'url' => '/api/v1/transactions/1'],
            ['method' => 'delete', 'url' => '/api/v1/transactions/1'],
            ['method' => 'get', 'url' => '/api/v1/transactions/summary'],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->{$endpoint['method'] . 'Json'}($endpoint['url']);
            $response->assertStatus(401);
        }
    });

    test('transaction not found returns 404', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/transactions/999999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ]);
    });

});