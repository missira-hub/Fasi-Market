<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRevenue extends Model
{
    protected $fillable = ['order_id', 'amount', 'currency', 'status'];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
