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
     */
    public function run(): void
    {
        $employee1 = User::where('email', 'ahmad@laundry.com')->first();
        $employee2 = User::where('email', 'siti@laundry.com')->first();

        Price::create([
            'user_id' => $employee1->id,
            'service_type' => 'Cuci Kering',
            'price' => 5000,
            'duration' => 1,
            'is_active' => true
        ]);

        Price::create([
            'user_id' => $employee1->id,
            'service_type' => 'Cuci Setrika',
            'price' => 7000,
            'duration' => 2,
            'is_active' => true
        ]);

        Price::create([
            'user_id' => $employee1->id,
            'service_type' => 'Setrika Saja',
            'price' => 3000,
            'duration' => 1,
            'is_active' => true
        ]);

        // Prices for employee 2
        Price::create([
            'user_id' => $employee2->id,
            'service_type' => 'Cuci Kering',
            'price' => 4500,
            'duration' => 1,
            'is_active' => true
        ]);

        Price::create([
            'user_id' => $employee2->id,
            'service_type' => 'Cuci Setrika',
            'price' => 6500,
            'duration' => 2,
            'is_active' => true
        ]);

        Price::create([
            'user_id' => $employee2->id,
            'service_type' => 'Express',
            'price' => 10000,
            'duration' => 1,
            'is_active' => true
        ]);
    }
}
