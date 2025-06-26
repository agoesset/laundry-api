<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel transactions (transaksis) untuk menyimpan data transaksi laundry
     * Berdasarkan struktur dari project laundry existing
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Identitas transaksi
            $table->string('invoice')->unique()->comment('Nomor invoice unik');
            $table->date('tgl_transaksi')->comment('Tanggal transaksi dibuat');
            
            // Foreign keys
            $table->unsignedBigInteger('customer_id')->comment('ID customer yang melakukan transaksi');
            $table->unsignedBigInteger('user_id')->comment('ID admin/karyawan yang memproses');
            $table->unsignedBigInteger('price_id')->comment('ID harga yang digunakan');
            
            // Detail customer (denormalized untuk kemudahan)
            $table->string('customer_name')->comment('Nama customer');
            $table->string('customer_email')->comment('Email customer');
            
            // Detail transaksi
            $table->decimal('kg', 5, 2)->comment('Berat laundry dalam kg');
            $table->integer('hari')->comment('Target hari selesai');
            $table->decimal('harga', 10, 2)->comment('Harga satuan per kg');
            $table->decimal('discount', 10, 2)->default(0)->comment('Diskon dalam rupiah');
            $table->decimal('total_harga', 10, 2)->comment('Total harga setelah diskon');
            
            // Status tracking
            $table->enum('status_order', ['Process', 'Done', 'Delivery'])->default('Process')->comment('Status pemrosesan order');
            $table->enum('status_payment', ['Pending', 'Success'])->default('Pending')->comment('Status pembayaran');
            $table->enum('payment_method', ['Cash', 'Transfer', 'E-Wallet'])->default('Cash')->comment('Metode pembayaran');
            
            // Tanggal penting
            $table->date('tgl_ambil')->nullable()->comment('Tanggal pengambilan laundry');
            
            // Tracking untuk laporan (denormalized)
            $table->integer('tgl')->comment('Tanggal (1-31) untuk laporan');
            $table->integer('bulan')->comment('Bulan (1-12) untuk laporan');
            $table->integer('tahun')->comment('Tahun untuk laporan');
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('price_id')->references('id')->on('prices')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes untuk optimasi query
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('price_id');
            $table->index('status_order');
            $table->index('status_payment');
            $table->index('tgl_transaksi');
            $table->index(['tahun', 'bulan', 'tgl']); // Composite index untuk laporan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
