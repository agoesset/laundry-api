<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat default users untuk testing
     * Admin, Karyawan, dan Customer
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'karyawan_id' => 'ADM001',
            'name' => 'Admin Laundry',
            'email' => 'admin@laundry.com',
            'password' => Hash::make('password123'),
            'auth' => 'Admin',
            'status' => 'Active',
            'no_telp' => '08123456789',
            'alamat' => 'Jl. Admin No. 1, Jakarta',
            'nama_cabang' => 'Laundry Pusat',
            'alamat_cabang' => 'Jl. Laundry Utama No. 10, Jakarta',
            'theme' => 'light',
            'point' => 0,
        ]);

        // Create Karyawan
        User::create([
            'karyawan_id' => 'KRY001',
            'name' => 'Karyawan Satu',
            'email' => 'karyawan@laundry.com',
            'password' => Hash::make('password123'),
            'auth' => 'Karyawan',
            'status' => 'Active',
            'no_telp' => '08123456790',
            'alamat' => 'Jl. Karyawan No. 1, Jakarta',
            'nama_cabang' => 'Laundry Cabang 1',
            'alamat_cabang' => 'Jl. Laundry Cabang No. 5, Jakarta',
            'theme' => 'light',
            'point' => 0,
        ]);

        // Create Customer
        User::create([
            'name' => 'Customer Test',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'auth' => 'Customer',
            'status' => 'Active',
            'no_telp' => '08123456791',
            'alamat' => 'Jl. Customer No. 1, Jakarta',
            'theme' => 'light',
            'point' => 100, // Customer dapat point dari transaksi
        ]);

        // Create inactive customer untuk testing
        User::create([
            'name' => 'Customer Inactive',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'auth' => 'Customer',
            'status' => 'Inactive',
            'no_telp' => '08123456792',
            'alamat' => 'Jl. Inactive No. 1, Jakarta',
            'theme' => 'light',
            'point' => 0,
        ]);

        $this->command->info('Users created successfully!');
        $this->command->table(
            ['Email', 'Password', 'Role', 'Status'],
            [
                ['admin@laundry.com', 'password123', 'Admin', 'Active'],
                ['karyawan@laundry.com', 'password123', 'Karyawan', 'Active'],
                ['customer@example.com', 'password123', 'Customer', 'Active'],
                ['inactive@example.com', 'password123', 'Customer', 'Inactive'],
            ]
        );
    }
}
