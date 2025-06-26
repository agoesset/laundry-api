<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Price untuk manajemen harga layanan laundry
 * 
 * Harga ditetapkan per kg dengan estimasi hari pengerjaan
 * Hanya Admin yang bisa mengatur harga
 */
class Price extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'prices';

    /**
     * The attributes that are mass assignable.
     * Field yang bisa diisi secara mass assignment
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',      // ID admin yang membuat harga
        'jenis',        // Jenis layanan (Cuci Kering, Cuci Setrika, dll)
        'kg',           // Berat minimal (per kg)
        'harga',        // Harga per kg dalam rupiah
        'hari',         // Estimasi hari selesai
        'status',       // Status: Active, Inactive
    ];

    /**
     * The attributes that should be cast.
     * Type casting untuk field tertentu
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',    // Format decimal dengan 2 angka di belakang koma
            'hari' => 'integer',       // Cast ke integer
        ];
    }

    /**
     * Relationship: Price belongs to User (Admin yang membuat)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Price memiliki banyak transactions
     * Transaksi yang menggunakan harga ini
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope: Hanya harga yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: Filter berdasarkan jenis layanan
     */
    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    /**
     * Scope: Urutkan berdasarkan harga termurah
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        return $query->orderBy('harga', $direction);
    }

    /**
     * Helper method: Format harga ke rupiah
     */
    public function getFormattedHargaAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Helper method: Get estimasi selesai dari tanggal tertentu
     */
    public function getEstimasiSelesai($tanggalMulai = null): string
    {
        $tanggal = $tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai) : \Carbon\Carbon::now();
        return $tanggal->addDays($this->hari)->format('d/m/Y');
    }

    /**
     * Helper method: Check apakah harga masih aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}
