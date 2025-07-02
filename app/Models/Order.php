<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice',
        'customer_id',
        'user_id',
        // tambah price_id
        'price_id',
        'customer_name',
        'customer_email',
        'order_date',
        'status',
        'payment_status',
        'weight',
        // hapus duration dan unit_price
        // 'duration',
        // 'unit_price',
        'discount',
        'total_amount',
        'payment_method',
        'pickup_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function getFormattedTotal()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
        //buat format ke rupiah
    }

     // Method untuk hitung estimasi selesai
     public function getEstimatedCompletion()
     {
         return $this->order_date->addDays($this->duration);
     }

     public static function generateInvoice()
     {
        $today = today();
        $count = static::whereDate('created_at', $today)->count() + 1;

        return 'INV-' . $today->format('Ymd') . '-' . str_pad($count, 3, 0, STR_PAD_LEFT);
     }
}
