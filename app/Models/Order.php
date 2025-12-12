<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'id'; // ← or 'order_id' — which one is it?
    public $incrementing = true;
    protected $keyType = 'int';

  protected $fillable = [
    'user_id',
    'total_price',
    'status',
    'delivery_method', // ← add this
    'shipped_at'
];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address()
    {
        return $this->hasOne(OrderAddress::class);
    }


    /**
     * Get the user who placed this order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function order_items()
{
    return $this->hasMany(\App\Models\OrderItem::class);
}

public function customer()
{
    return $this->belongsTo(User::class, 'user_id');
}


}
