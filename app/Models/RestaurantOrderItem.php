<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantOrderItem extends Model
{
    use HasFactory;

    const PENDING = 'PENDING';       // PENDIENTE
    const SENT = 'SENT';             // ENVIADO
    const READY = 'READY';           // LISTO
    const DELIVERED = 'DELIVERED';   // ENTREGADO
    const CANCELLED = 'CANCELLED';   // ANULADO

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
        'cancelled_by',
        'kds_destination',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_to_kitchen_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
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

    public function kitchenStatusLabel(): string
    {
        return match($this->kitchen_status) {
            'PENDING'   => 'PENDIENTE',
            'SENT'      => 'ENVIADO',
            'READY'     => 'LISTO',
            'DELIVERED' => 'ENTREGADO',
            'CANCELLED' => 'ANULADO',
            default     => $this->kitchen_status,
        };
    }
}
