<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat sample harga layanan laundry
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('auth', 'Admin')->first();
        
        if (!$admin) {
            $this->command->error('Admin user not found! Run UserSeeder first.');
            return;
        }
        
        $prices = [
            [
                'user_id' => $admin->id,
                'jenis' => 'Cuci Kering',
                'kg' => '1 kg',
                'harga' => 5000.00,
                'hari' => 1,
                'status' => 'Active',
            ],
            [
                'user_id' => $admin->id,
                'jenis' => 'Cuci Setrika',
                'kg' => '1 kg',
                'harga' => 7000.00,
                'hari' => 2,
                'status' => 'Active',
            ],
            [
                'user_id' => $admin->id,
                'jenis' => 'Cuci Lipat',
                'kg' => '1 kg',
                'harga' => 6000.00,
                'hari' => 1,
                'status' => 'Active',
            ],
            [
                'user_id' => $admin->id,
                'jenis' => 'Dry Clean',
                'kg' => '1 kg',
                'harga' => 15000.00,
                'hari' => 3,
                'status' => 'Active',
            ],
            [
                'user_id' => $admin->id,
                'jenis' => 'Express',
                'kg' => '1 kg',
                'harga' => 10000.00,
                'hari' => 1,
                'status' => 'Active',
            ],
        ];
        
        foreach ($prices as $price) {
            Price::create($price);
        }
        
        $this->command->info('Sample prices created successfully!');
        $this->command->table(
            ['Jenis', 'Harga', 'Hari', 'Status'],
            collect($prices)->map(function ($price) {
                return [
                    $price['jenis'],
                    'Rp ' . number_format($price['harga'], 0, ',', '.'),
                    $price['hari'] . ' hari',
                    $price['status']
                ];
            })->toArray()
        );
    }
}
