<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model User untuk sistem authentication dan manajemen user
 * 
 * Supports 3 role: Admin, Customer, Karyawan
 * Terintegrasi dengan Laravel Sanctum untuk API authentication
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     * Field yang bisa diisi secara mass assignment
     *
     * @var list<string>
     */
    protected $fillable = [
        'karyawan_id',      // ID karyawan untuk staff/admin
        'name',             // Nama lengkap user
        'email',            // Email untuk login
        'password',         // Password terenkripsi
        'alamat',           // Alamat lengkap user
        'no_telp',          // Nomor telepon
        'foto',             // Path foto profile
        'auth',             // Role: Admin, Customer, Karyawan
        'status',           // Status: Active, Inactive
        'nama_cabang',      // Nama cabang untuk admin/karyawan
        'alamat_cabang',    // Alamat cabang
        'theme',            // Theme preference user
        'point',            // Point reward customer
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Field yang disembunyikan dalam response API
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * Type casting untuk field tertentu
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'point' => 'integer',
        ];
    }

    /**
     * Relationship: User memiliki banyak prices (harga layanan)
     * Hanya Admin yang bisa membuat harga
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Relationship: User memiliki banyak transactions sebagai staff/admin
     * Yang memproses transaksi
     */
    public function processedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * Relationship: Customer memiliki banyak transactions
     * Yang melakukan transaksi
     */
    public function customerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    /**
     * Relationship: User memiliki banyak bank accounts
     * Untuk pembayaran dan transfer
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Relationship: User memiliki satu primary bank account
     * Rekening utama yang aktif
     */
    public function primaryBankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class)->where('is_primary', true);
    }

    /**
     * Relationship: User memiliki banyak laundry settings
     * Admin bisa mengatur konfigurasi sistem
     */
    public function laundrySettings(): HasMany
    {
        return $this->hasMany(LaundrySetting::class);
    }

    /**
     * Scope: Hanya user dengan role tertentu
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('auth', $role);
    }

    /**
     * Scope: Hanya user yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Helper method: Check apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->auth === 'Admin';
    }

    /**
     * Helper method: Check apakah user adalah customer
     */
    public function isCustomer(): bool
    {
        return $this->auth === 'Customer';
    }

    /**
     * Helper method: Check apakah user adalah karyawan
     */
    public function isKaryawan(): bool
    {
        return $this->auth === 'Karyawan';
    }

    /**
     * Helper method: Get foto URL lengkap
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
