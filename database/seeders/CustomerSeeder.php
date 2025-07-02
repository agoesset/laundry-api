<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee1 = User::where('email', 'ahmad@laundry.com')->first();
        $employee2 = User::where('email', 'siti@laundry.com')->first();

         // Customers for employee 1
         Customer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567001',
            'address' => 'Jl. Kebon Jeruk No. 15, Jakarta',
            'user_id' => $employee1->id
        ]);

        Customer::create([
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'phone' => '081234567002',
            'address' => 'Jl. Mangga Dua No. 28, Jakarta',
            'user_id' => $employee1->id
        ]);

        Customer::create([
            'name' => 'Agus Pratama',
            'email' => 'agus@example.com',
            'phone' => '081234567003',
            'address' => 'Jl. Kemang Raya No. 45, Jakarta',
            'user_id' => $employee1->id
        ]);

        // Customers for employee 2
        Customer::create([
            'name' => 'Linda Sari',
            'email' => 'linda@example.com',
            'phone' => '081234567004',
            'address' => 'Jl. Pondok Indah No. 12, Jakarta',
            'user_id' => $employee2->id
        ]);

        Customer::create([
            'name' => 'Rudi Hermawan',
            'email' => 'rudi@example.com',
            'phone' => '081234567005',
            'address' => 'Jl. Cipete Raya No. 33, Jakarta',
            'user_id' => $employee2->id
        ]);

        Customer::create([
            'name' => 'Maya Indira',
            'email' => 'maya@example.com',
            'phone' => '081234567006',
            'address' => 'Jl. Kebayoran Baru No. 78, Jakarta',
            'user_id' => $employee2->id
        ]);
    }
}
