<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('user_id')->constrained('users');
            // tambah price_id
            $table->foreignId('price_id')->constrained('prices');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->date('order_date');
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid']);
            $table->decimal('weight', 8, 2); // per kg, misal 2.50 kg
            // hapus field duration dan unit_price
            $table->integer('discount')->nullable();
            $table->integer('total_amount'); //total harga
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->dateTime('pickup_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
