<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'delivery_method',
        'shipped_at'
    ];

    // ✅ ONLY this relationship for items
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function address()
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // If customers are regular 'users'
public function customer()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}
}