<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel prices (hargas) untuk menyimpan harga layanan laundry
     * Berdasarkan struktur dari project laundry existing
     */
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke users (admin/owner yang membuat harga)
            $table->unsignedBigInteger('user_id')->comment('ID user yang membuat harga');
            
            // Detail harga layanan
            $table->string('jenis')->comment('Jenis layanan (Cuci Kering, Cuci Setrika, dll)');
            $table->string('kg')->comment('Berat minimal (per kg)');
            $table->decimal('harga', 10, 2)->comment('Harga per kg dalam rupiah');
            $table->integer('hari')->comment('Estimasi hari selesai');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->comment('Status aktif harga');
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes untuk optimasi query
            $table->index('user_id');
            $table->index('status');
            $table->index('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
