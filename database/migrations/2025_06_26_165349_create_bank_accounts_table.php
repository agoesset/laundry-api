<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel bank_accounts (data_banks) untuk menyimpan info rekening bank
     * Berdasarkan struktur dari project laundry existing
     */
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke users (admin/owner yang memiliki rekening)
            $table->unsignedBigInteger('user_id')->comment('ID user pemilik rekening');
            
            // Detail bank
            $table->string('bank_name')->comment('Nama bank (BCA, BRI, Mandiri, dll)');
            $table->string('account_number')->comment('Nomor rekening');
            $table->string('account_holder')->comment('Nama pemegang rekening');
            
            // Status dan metadata
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->comment('Status aktif rekening');
            $table->boolean('is_primary')->default(false)->comment('Apakah rekening utama');
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes untuk optimasi query
            $table->index('user_id');
            $table->index('status');
            $table->index('is_primary');
            
            // Unique constraint untuk mencegah duplikasi nomor rekening
            $table->unique(['bank_name', 'account_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
