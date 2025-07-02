<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'user_id',
        'service_type',
        'price',
        'duration',
        'is_active',
    ];

    protected function casts(): array
    {
        return[
            'is_active' => 'boolean'
        ];
    }

    //Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
        // employee yang buat price
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
        // price ini bisa di pakai oleh banyak order (order 1, 2, dst)
    }

    public function getFormattedPrice()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
        //buat format ke rupiah
    }
}
