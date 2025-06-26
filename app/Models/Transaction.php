<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Model Transaction untuk manajemen transaksi laundry
 * 
 * Menangani semua transaksi dari customer dengan tracking status
 * lengkap dari order hingga delivery
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     * Field yang bisa diisi secara mass assignment
     *
     * @var array<string>
     */
    protected $fillable = [
        'invoice',          // Nomor invoice unik
        'tgl_transaksi',    // Tanggal transaksi dibuat
        'customer_id',      // ID customer yang melakukan transaksi
        'user_id',          // ID admin/karyawan yang memproses
        'price_id',         // ID harga yang digunakan
        'customer_name',    // Nama customer (denormalized)
        'customer_email',   // Email customer (denormalized)
        'kg',               // Berat laundry dalam kg
        'hari',             // Target hari selesai
        'harga',            // Harga satuan per kg
        'discount',         // Diskon dalam rupiah
        'total_harga',      // Total harga setelah diskon
        'status_order',     // Status pemrosesan order
        'status_payment',   // Status pembayaran
        'payment_method',   // Metode pembayaran
        'tgl_ambil',        // Tanggal pengambilan laundry
        'tgl',              // Tanggal (1-31) untuk laporan
        'bulan',            // Bulan (1-12) untuk laporan
        'tahun',            // Tahun untuk laporan
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
            'tgl_transaksi' => 'date',      // Cast ke tanggal
            'tgl_ambil' => 'date',          // Cast ke tanggal
            'kg' => 'decimal:2',            // Format decimal 2 digit
            'harga' => 'decimal:2',         // Format decimal 2 digit
            'discount' => 'decimal:2',      // Format decimal 2 digit
            'total_harga' => 'decimal:2',   // Format decimal 2 digit
            'hari' => 'integer',            // Cast ke integer
            'tgl' => 'integer',             // Cast ke integer
            'bulan' => 'integer',           // Cast ke integer
            'tahun' => 'integer',           // Cast ke integer
        ];
    }

    /**
     * Relationship: Transaction belongs to Customer (User)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Relationship: Transaction belongs to User (Admin/Karyawan yang memproses)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Transaction belongs to Price
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    /**
     * Scope: Filter berdasarkan status order
     */
    public function scopeByOrderStatus($query, string $status)
    {
        return $query->where('status_order', $status);
    }

    /**
     * Scope: Filter berdasarkan status payment
     */
    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('status_payment', $status);
    }

    /**
     * Scope: Filter berdasarkan customer
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: Filter berdasarkan tanggal
     */
    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('tgl_transaksi', $date);
    }

    /**
     * Scope: Filter berdasarkan bulan dan tahun
     */
    public function scopeByMonth($query, int $bulan, int $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    /**
     * Scope: Transaksi yang sudah selesai
     */
    public function scopeCompleted($query)
    {
        return $query->where('status_order', 'Done');
    }

    /**
     * Scope: Transaksi yang belum dibayar
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status_payment', 'Pending');
    }

    /**
     * Helper method: Generate invoice number otomatis
     */
    public static function generateInvoice(): string
    {
        $date = Carbon::now()->format('Ymd');
        $count = self::whereDate('created_at', Carbon::today())->count() + 1;
        return 'LND-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Helper method: Hitung total harga (harga * kg - discount)
     */
    public function calculateTotal(): float
    {
        return ($this->harga * $this->kg) - $this->discount;
    }

    /**
     * Helper method: Get estimasi tanggal selesai
     */
    public function getEstimasiSelesaiAttribute(): string
    {
        return $this->tgl_transaksi->addDays($this->hari)->format('d/m/Y');
    }

    /**
     * Helper method: Get status order dalam bahasa Indonesia
     */
    public function getStatusOrderTextAttribute(): string
    {
        return match($this->status_order) {
            'Process' => 'Sedang Diproses',
            'Done' => 'Selesai',
            'Delivery' => 'Siap Diambil',
            default => 'Unknown'
        };
    }

    /**
     * Helper method: Get status payment dalam bahasa Indonesia
     */
    public function getStatusPaymentTextAttribute(): string
    {
        return match($this->status_payment) {
            'Pending' => 'Belum Dibayar',
            'Success' => 'Sudah Dibayar',
            default => 'Unknown'
        };
    }

    /**
     * Helper method: Check apakah transaksi sudah selesai
     */
    public function isCompleted(): bool
    {
        return $this->status_order === 'Done';
    }

    /**
     * Helper method: Check apakah transaksi sudah dibayar
     */
    public function isPaid(): bool
    {
        return $this->status_payment === 'Success';
    }

    /**
     * Helper method: Format total harga ke rupiah
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    /**
     * Boot method untuk auto-fill tanggal fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->invoice) {
                $transaction->invoice = self::generateInvoice();
            }
            
            if (!$transaction->tgl_transaksi) {
                $transaction->tgl_transaksi = Carbon::now()->toDateString();
            }

            // Auto-fill tanggal fields untuk laporan
            $date = Carbon::parse($transaction->tgl_transaksi);
            $transaction->tgl = $date->day;
            $transaction->bulan = $date->month;
            $transaction->tahun = $date->year;

            // Auto-calculate total jika belum diset
            if (!$transaction->total_harga) {
                $transaction->total_harga = ($transaction->harga * $transaction->kg) - $transaction->discount;
            }
        });
    }
}
