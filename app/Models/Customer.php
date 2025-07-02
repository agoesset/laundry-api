<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'user_id',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
        // customer memiliko 1 user (1 customer dibuat oleh 1 admin/employee)
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
        // 1 customer bisa punya banyak order
    }

    public function getTotalSpent()
    {
        return $this->orders()->sum('total_amount');
        // cara panggil = $customer->getTotalSpent() = total uang yang di habiskan customer
    }
}
