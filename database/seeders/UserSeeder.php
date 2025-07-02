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
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Laundry',
            'email' => 'admin@laundry.com',
            'password' => Hash::make('bismillah123'),
            'role' => 'admin',
            'is_active' => true,
            'branch_name' => 'Cabang Jakarta',
            'branch_address' => 'Jl. Sudirman No. 123, Jakarta',
            'phone' => '085804686544',
        ]);

        // Employee users
        User::create([
            'name' => 'Ahmad Employee',
            'email' => 'ahmad@laundry.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
            'branch_name' => 'Branch Bandung',
            'branch_address' => 'Jl. Kemerdekaan No. 45, Banung',
            'phone' => '081234567891'
        ]);

        User::create([
            'name' => 'Siti Employee',
            'email' => 'siti@laundry.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
            'branch_name' => 'Branch Solo',
            'branch_address' => 'Jl. Gatot Subroto No. 67, Solo',
            'phone' => '081234567892'
        ]);
    }
}
