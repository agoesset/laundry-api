<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model LaundrySetting untuk pengaturan sistem laundry
 * 
 * Menyimpan konfigurasi operasional, notifikasi, dan pengaturan bisnis
 * Setiap admin bisa membuat setting sendiri tapi hanya 1 yang aktif
 */
class LaundrySetting extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'laundry_settings';

    /**
     * The attributes that are mass assignable.
     * Field yang bisa diisi secara mass assignment
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',                  // ID admin yang mengatur
        'company_name',             // Nama perusahaan laundry
        'company_address',          // Alamat perusahaan
        'company_phone',            // Telepon perusahaan
        'company_email',            // Email perusahaan
        'opening_time',             // Jam buka
        'closing_time',             // Jam tutup
        'working_days',             // Hari kerja (JSON array)
        'invoice_prefix',           // Prefix nomor invoice
        'invoice_counter',          // Counter untuk nomor invoice
        'whatsapp_notification',    // Aktifkan notifikasi WhatsApp
        'whatsapp_token',           // Token WhatsApp API
        'email_notification',       // Aktifkan notifikasi Email
        'telegram_notification',    // Aktifkan notifikasi Telegram
        'telegram_token',           // Token Telegram Bot
        'telegram_chat_id',         // Chat ID Telegram
        'minimum_order',            // Minimal order dalam kg
        'allow_discount',           // Izinkan diskon
        'max_discount_percent',     // Maksimal diskon dalam persen
        'is_active',                // Status aktif setting
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
            'working_days' => 'array',              // Cast JSON ke array
            'opening_time' => 'datetime:H:i',       // Format jam
            'closing_time' => 'datetime:H:i',       // Format jam
            'invoice_counter' => 'integer',         // Cast ke integer
            'whatsapp_notification' => 'boolean',   // Cast ke boolean
            'email_notification' => 'boolean',      // Cast ke boolean
            'telegram_notification' => 'boolean',   // Cast ke boolean
            'minimum_order' => 'decimal:2',         // Format decimal
            'allow_discount' => 'boolean',          // Cast ke boolean
            'max_discount_percent' => 'decimal:2',  // Format decimal
            'is_active' => 'boolean',               // Cast ke boolean
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     * Field yang disembunyikan dalam response API
     *
     * @var list<string>
     */
    protected $hidden = [
        'whatsapp_token',   // Sembunyikan token WhatsApp
        'telegram_token',   // Sembunyikan token Telegram
    ];

    /**
     * Relationship: LaundrySetting belongs to User (Admin)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Hanya setting yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper method: Get hari kerja dalam bahasa Indonesia
     */
    public function getWorkingDaysTextAttribute(): array
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $workingDays = [];
        foreach ($this->working_days as $day) {
            if (isset($days[$day])) {
                $workingDays[] = $days[$day];
            }
        }

        return $workingDays;
    }

    /**
     * Helper method: Check apakah hari tertentu adalah hari kerja
     */
    public function isWorkingDay(int $dayNumber): bool
    {
        return in_array($dayNumber, $this->working_days);
    }

    /**
     * Helper method: Check apakah toko buka pada waktu tertentu
     */
    public function isOpen($time = null): bool
    {
        $currentTime = $time ? \Carbon\Carbon::parse($time) : \Carbon\Carbon::now();
        $currentDay = $currentTime->dayOfWeekIso; // 1 = Monday, 7 = Sunday

        // Check apakah hari kerja
        if (!$this->isWorkingDay($currentDay)) {
            return false;
        }

        // Check jam operasional
        $openingTime = \Carbon\Carbon::parse($this->opening_time);
        $closingTime = \Carbon\Carbon::parse($this->closing_time);
        
        return $currentTime->between($openingTime, $closingTime);
    }

    /**
     * Helper method: Get next invoice number
     */
    public function getNextInvoiceNumber(): string
    {
        $counter = $this->invoice_counter;
        $date = \Carbon\Carbon::now()->format('Ymd');
        
        return $this->invoice_prefix . '-' . $date . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Helper method: Increment invoice counter
     */
    public function incrementInvoiceCounter(): void
    {
        $this->increment('invoice_counter');
    }

    /**
     * Helper method: Check apakah notifikasi WhatsApp aktif
     */
    public function isWhatsAppEnabled(): bool
    {
        return $this->whatsapp_notification && !empty($this->whatsapp_token);
    }

    /**
     * Helper method: Check apakah notifikasi Telegram aktif
     */
    public function isTelegramEnabled(): bool
    {
        return $this->telegram_notification && 
               !empty($this->telegram_token) && 
               !empty($this->telegram_chat_id);
    }

    /**
     * Helper method: Check apakah notifikasi Email aktif
     */
    public function isEmailEnabled(): bool
    {
        return $this->email_notification && !empty($this->company_email);
    }

    /**
     * Helper method: Get active setting
     */
    public static function getActive(): ?self
    {
        return self::active()->first();
    }

    /**
     * Boot method untuk validasi active setting
     */
    protected static function boot()
    {
        parent::boot();

        // Pastikan hanya ada 1 active setting
        static::creating(function ($setting) {
            if ($setting->is_active) {
                self::where('is_active', true)->update(['is_active' => false]);
            }
        });

        static::updating(function ($setting) {
            if ($setting->is_active && $setting->isDirty('is_active')) {
                self::where('id', '!=', $setting->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }
}
