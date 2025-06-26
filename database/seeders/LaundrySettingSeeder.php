<?php

namespace Database\Seeders;

use App\Models\LaundrySetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaundrySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat pengaturan default untuk sistem laundry
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('auth', 'Admin')->first();
        
        if (!$admin) {
            $this->command->error('Admin user not found! Run UserSeeder first.');
            return;
        }
        
        // Buat setting default
        LaundrySetting::create([
            'user_id' => $admin->id,
            'company_name' => 'Laundry API',
            'company_address' => 'Jl. Contoh Alamat No. 123, Jakarta',
            'company_phone' => '081234567890',
            'company_email' => 'admin@laundry.com',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'working_days' => json_encode([1, 2, 3, 4, 5, 6]), // Monday to Saturday
            'invoice_prefix' => 'LND',
            'invoice_counter' => 1,
            'whatsapp_notification' => false,
            'whatsapp_token' => null,
            'email_notification' => true,
            'telegram_notification' => false,
            'telegram_token' => null,
            'telegram_chat_id' => null,
            'minimum_order' => 10000.00, // Rp 10,000
            'allow_discount' => true,
            'max_discount_percent' => 20.00, // 20%
            'is_active' => true,
        ]);
        
        $this->command->info('Laundry settings created successfully!');
        $this->command->table(
            ['Setting', 'Value'],
            [
                ['Nama Laundry', 'Laundry API'],
                ['Minimum Order', 'Rp 10,000'],
                ['Diskon Max', '20%'],
                ['Jam Operasional', '08:00 - 20:00'],
                ['Working Days', '6 hari (Senin - Sabtu)'],
            ]
        );
    }
}