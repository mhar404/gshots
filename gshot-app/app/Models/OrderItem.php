<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Product;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'name', 'quantity'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
