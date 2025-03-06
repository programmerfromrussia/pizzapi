<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Location;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'status',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    /**
     * Get the cart that owns the order.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the location for the order.
     */
    public function location(): HasOne
    {
        return $this->hasOne(Location::class);
    }
}
