<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    protected $fillable = ['name', 'phone', 'payment', 'address', 'total'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
