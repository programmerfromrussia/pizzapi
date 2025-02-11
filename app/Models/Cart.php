<?php

namespace App\Models;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function cartItem()
    {
        return $this->hasMany(CartItem::class);
    }
}
