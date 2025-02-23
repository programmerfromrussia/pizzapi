<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'city',
        'country',
        'order_id',
    ];

    /**
     * Get the order that owns the location.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
