<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total',
        'kitchen_status',
        'sent_to_kitchen_at',
        'notes',
        'cancelled_from',
        'cancelled_at',
        'kds_destination',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_to_kitchen_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopePending($query)
    {
        return $query->where('kitchen_status', 'PENDING');
    }

    public function scopeSent($query)
    {
        return $query->where('kitchen_status', 'SENT');
    }

    public function scopeReady($query)
    {
        return $query->where('kitchen_status', 'READY');
    }

    public function scopeDelivered($query)
    {
        return $query->where('kitchen_status', 'DELIVERED');
    }
}
