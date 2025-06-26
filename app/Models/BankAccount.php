<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model BankAccount untuk manajemen rekening bank
 * 
 * Digunakan untuk menyimpan informasi rekening bank admin/owner
 * untuk keperluan pembayaran dan transfer
 */
class BankAccount extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'bank_accounts';

    /**
     * The attributes that are mass assignable.
     * Field yang bisa diisi secara mass assignment
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',          // ID user pemilik rekening
        'bank_name',        // Nama bank (BCA, BRI, Mandiri, dll)
        'account_number',   // Nomor rekening
        'account_holder',   // Nama pemegang rekening
        'status',           // Status: Active, Inactive
        'is_primary',       // Apakah rekening utama
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
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Relationship: BankAccount belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Hanya rekening yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: Hanya rekening utama
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: Filter berdasarkan nama bank
     */
    public function scopeByBank($query, string $bankName)
    {
        return $query->where('bank_name', $bankName);
    }

    /**
     * Helper method: Check apakah rekening aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Helper method: Check apakah rekening utama
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Helper method: Format nomor rekening dengan mask
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $number = $this->account_number;
        $length = strlen($number);
        
        if ($length <= 4) {
            return $number;
        }

        $start = substr($number, 0, 4);
        $end = substr($number, -4);
        $middle = str_repeat('*', $length - 8);
        
        return $start . $middle . $end;
    }

    /**
     * Helper method: Get informasi rekening lengkap
     */
    public function getFullAccountInfoAttribute(): string
    {
        return $this->bank_name . ' - ' . $this->account_holder . ' (' . $this->masked_account_number . ')';
    }

    /**
     * Boot method untuk validasi primary account
     */
    protected static function boot()
    {
        parent::boot();

        // Pastikan hanya ada 1 primary account per user
        static::creating(function ($bankAccount) {
            if ($bankAccount->is_primary) {
                self::where('user_id', $bankAccount->user_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        static::updating(function ($bankAccount) {
            if ($bankAccount->is_primary && $bankAccount->isDirty('is_primary')) {
                self::where('user_id', $bankAccount->user_id)
                    ->where('id', '!=', $bankAccount->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
