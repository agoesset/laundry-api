<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel laundry_settings untuk menyimpan pengaturan sistem
     * Berdasarkan struktur dari project laundry existing
     */
    public function up(): void
    {
        Schema::create('laundry_settings', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke users (admin yang membuat setting)
            $table->unsignedBigInteger('user_id')->comment('ID admin yang mengatur');
            
            // Informasi bisnis
            $table->string('company_name')->comment('Nama perusahaan laundry');
            $table->text('company_address')->comment('Alamat perusahaan');
            $table->string('company_phone')->comment('Telepon perusahaan');
            $table->string('company_email')->nullable()->comment('Email perusahaan');
            
            // Pengaturan operasional
            $table->time('opening_time')->default('08:00:00')->comment('Jam buka');
            $table->time('closing_time')->default('20:00:00')->comment('Jam tutup');
            $table->json('working_days')->comment('Hari kerja (array: [1,2,3,4,5,6,7])');
            
            // Pengaturan invoice
            $table->string('invoice_prefix')->default('LND')->comment('Prefix nomor invoice');
            $table->integer('invoice_counter')->default(1)->comment('Counter untuk nomor invoice');
            
            // Pengaturan notifikasi
            $table->boolean('whatsapp_notification')->default(false)->comment('Aktifkan notifikasi WhatsApp');
            $table->string('whatsapp_token')->nullable()->comment('Token WhatsApp API');
            $table->boolean('email_notification')->default(false)->comment('Aktifkan notifikasi Email');
            $table->boolean('telegram_notification')->default(false)->comment('Aktifkan notifikasi Telegram');
            $table->string('telegram_token')->nullable()->comment('Token Telegram Bot');
            $table->string('telegram_chat_id')->nullable()->comment('Chat ID Telegram');
            
            // Pengaturan pembayaran
            $table->decimal('minimum_order', 10, 2)->default(1.00)->comment('Minimal order dalam kg');
            $table->boolean('allow_discount')->default(true)->comment('Izinkan diskon');
            $table->decimal('max_discount_percent', 5, 2)->default(10.00)->comment('Maksimal diskon dalam persen');
            
            // Meta
            $table->boolean('is_active')->default(true)->comment('Status aktif setting');
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_settings');
    }
};
