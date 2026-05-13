<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'table_id',
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'igv',
        'total',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class, 'restaurant_order_id');
    }

    public function getPendingItemsAttribute()
    {
        return $this->items->where('kitchen_status', 'PENDING');
    }

    public function getSentToKitchenItemsAttribute()
    {
        return $this->items->where('kitchen_status', 'SENT');
    }

    public function getReadyItemsAttribute()
    {
        return $this->items->where('kitchen_status', 'READY');
    }

    public function hasPendingKitchenItems(): bool
    {
        return $this->items->whereIn('kitchen_status', ['PENDING', 'SENT'])->isNotEmpty();
    }

    public function allItemsDelivered(): bool
    {
        return $this->items->where('kitchen_status', '!=', 'DELIVERED')->isEmpty();
    }

    public static function generateOrderNumber(): string
    {
        $companyId = auth()->check() ? auth()->user()->company_id : 1;
        $date = now()->format('Ymd');
        $lastOrder = self::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastOrder && preg_match('/-(\d+)$/', $lastOrder->order_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }
        
        return 'P-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
