<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel users untuk sistem authentication
     * Berdasarkan struktur dari project laundry existing
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Field untuk identitas user
            $table->string('karyawan_id')->nullable()->comment('ID karyawan untuk staff/admin');
            $table->string('name')->comment('Nama lengkap user');
            $table->string('email')->unique()->comment('Email untuk login');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->comment('Password terenkripsi');
            
            // Field untuk profile dan kontak
            $table->text('alamat')->nullable()->comment('Alamat lengkap user');
            $table->string('no_telp')->nullable()->comment('Nomor telepon');
            $table->string('foto')->nullable()->comment('Path foto profile');
            
            // Field untuk business logic
            $table->enum('auth', ['Admin', 'Customer', 'Karyawan'])->default('Customer')->comment('Role user dalam sistem');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->comment('Status aktif user');
            $table->string('nama_cabang')->nullable()->comment('Nama cabang untuk admin/karyawan');
            $table->text('alamat_cabang')->nullable()->comment('Alamat cabang');
            $table->string('theme')->default('light')->comment('Theme preference user');
            $table->integer('point')->default(0)->comment('Point reward customer');
            
            // Laravel defaults
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes untuk optimasi query
            $table->index('auth');
            $table->index('status');
            $table->index('karyawan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
