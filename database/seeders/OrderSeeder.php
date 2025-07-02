<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Get existing customers, users, and prices
         $customers = Customer::all();
         $users = User::all();
         $prices = Price::where('is_active', true)->get();
 
         if ($customers->isEmpty() || $users->isEmpty() || $prices->isEmpty()) {
             $this->command->warn('Please ensure CustomerSeeder, UserSeeder, and PriceSeeder have been run first.');
             return;
         }
 
         $orders = [
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Ahmad Wijaya',
                 'customer_email' => 'ahmad.wijaya@email.com',
                 'order_date' => now()->subDays(5),
                 'status' => 'completed',
                 'payment_status' => 'paid',
                 'weight' => 3.5,
                 'discount' => 0,
                 'total_amount' => 52500,
                 'payment_method' => 'cash',
                 'pickup_date' => now()->subDays(2),
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Sari Indah',
                 'customer_email' => 'sari.indah@email.com',
                 'order_date' => now()->subDays(3),
                 'status' => 'processing',
                 'payment_status' => 'paid',
                 'weight' => 2.0,
                 'discount' => 5000,
                 'total_amount' => 25000,
                 'payment_method' => 'transfer',
                 'pickup_date' => null,
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Budi Santoso',
                 'customer_email' => 'budi.santoso@email.com',
                 'order_date' => now()->subDays(1),
                 'status' => 'pending',
                 'payment_status' => 'unpaid',
                 'weight' => 4.2,
                 'discount' => 0,
                 'total_amount' => 63000,
                 'payment_method' => 'cash',
                 'pickup_date' => null,
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Lisa Permata',
                 'customer_email' => 'lisa.permata@email.com',
                 'order_date' => now()->subDays(7),
                 'status' => 'completed',
                 'payment_status' => 'paid',
                 'weight' => 1.8,
                 'discount' => 2000,
                 'total_amount' => 25000,
                 'payment_method' => 'transfer',
                 'pickup_date' => now()->subDays(4),
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Riko Firmansyah',
                 'customer_email' => 'riko.firmansyah@email.com',
                 'order_date' => now(),
                 'status' => 'pending',
                 'payment_status' => 'unpaid',
                 'weight' => 5.0,
                 'discount' => 0,
                 'total_amount' => 75000,
                 'payment_method' => 'cash',
                 'pickup_date' => null,
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Maya Sari',
                 'customer_email' => 'maya.sari@email.com',
                 'order_date' => now()->subDays(4),
                 'status' => 'processing',
                 'payment_status' => 'paid',
                 'weight' => 2.7,
                 'discount' => 3000,
                 'total_amount' => 37500,
                 'payment_method' => 'transfer',
                 'pickup_date' => null,
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Andi Pratama',
                 'customer_email' => 'andi.pratama@email.com',
                 'order_date' => now()->subDays(6),
                 'status' => 'completed',
                 'payment_status' => 'paid',
                 'weight' => 3.2,
                 'discount' => 0,
                 'total_amount' => 48000,
                 'payment_method' => 'cash',
                 'pickup_date' => now()->subDays(3),
             ],
             [
                 'customer_id' => $customers->random()->id,
                 'user_id' => $users->random()->id,
                 'price_id' => $prices->random()->id,
                 'customer_name' => 'Dewi Lestari',
                 'customer_email' => 'dewi.lestari@email.com',
                 'order_date' => now()->subDays(2),
                 'status' => 'processing',
                 'payment_status' => 'unpaid',
                 'weight' => 1.5,
                 'discount' => 1000,
                 'total_amount' => 21000,
                 'payment_method' => 'cash',
                 'pickup_date' => null,
             ],
         ];
 
         foreach ($orders as $orderData) {
             
             $orderData['invoice'] = Order::generateInvoice();
             Order::create($orderData);
         }
     }
}