<?php

namespace App\Models;

use App\Models\Location;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function location()
    {
        return $this->hasOne(Location::class);
    }
}
