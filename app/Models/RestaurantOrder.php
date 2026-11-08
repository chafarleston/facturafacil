<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class RestaurantOrder extends Model
{
    use HasFactory;

    // Estados del pedido
    const STATUS_OPEN = 'OPEN';               // ABIERTO
    const STATUS_SENT_TO_KITCHEN = 'SENT_TO_KITCHEN'; // ENVIADO A COCINA
    const STATUS_PREPARING = 'PREPARING';     // EN PREPARACIÓN
    const STATUS_READY = 'READY';             // LISTO (legado)
    const STATUS_DELIVERED = 'DELIVERED';     // ENTREGADO
    const STATUS_COMPLETED = 'COMPLETED';     // COMPLETADO
    const STATUS_CANCELLED = 'CANCELLED';     // ANULADO

    // Estados de items en cocina
    const ITEM_PENDING = 'PENDING';           // PENDIENTE
    const ITEM_SENT = 'SENT';                 // ENVIADO
    const ITEM_READY = 'READY';               // LISTO
    const ITEM_DELIVERED = 'DELIVERED';       // ENTREGADO
    const ITEM_CANCELLED = 'CANCELLED';       // ANULADO

    protected $fillable = [
        'company_id',
        'table_id',
        'user_id',
        'order_number',
        'legacy_order_number',
        'status',
        'order_type',
        'delivery_type',
        'customer_name',
        'delivery_address',
        'delivery_reference',
        'contact_number',
        'sent_to_kitchen_at',
        'preparing_started_at',
        'finished_at',
        'subtotal',
        'igv',
        'total',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_to_kitchen_at' => 'datetime',
        'preparing_started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $appends = ['version'];

    public function getVersionAttribute(): string
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        $payload = [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'subtotal' => (string) $this->subtotal,
            'igv' => (string) $this->igv,
            'total' => (string) $this->total,
            'updated_at' => $this->updated_at?->toJSON(),
            'items' => $items->sortBy('id')->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'total' => (string) $item->total,
                'kitchen_status' => $item->kitchen_status,
                'paid_invoice_id' => $item->paid_invoice_id,
                'notes' => $item->notes,
                'auxiliary_items' => $item->auxiliary_items,
                'sent_to_kitchen_at' => $item->sent_to_kitchen_at?->toJSON(),
                'cancelled_at' => $item->cancelled_at?->toJSON(),
            ])->values()->all(),
        ];

        return sha1(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected static function booted(): void
    {
        static::updated(function (self $order) {
            if (!$order->wasChanged('status')) {
                return;
            }

            Log::info('Restaurant order status changed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'table_id' => $order->table_id,
                'order_type' => $order->order_type,
                'from' => $order->getOriginal('status'),
                'to' => $order->status,
                'user_id' => auth()->id(),
            ]);
        });

        static::deleted(function (self $order) {
            Log::warning('Restaurant order deleted', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'table_id' => $order->table_id,
                'status' => $order->status,
                'user_id' => auth()->id(),
            ]);
        });
    }

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

    public function kitchenBatches(): HasMany
    {
        return $this->hasMany(RestaurantKitchenBatch::class, 'restaurant_order_id');
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

    public static function generateOrderNumber(?int $companyId = null): string
    {
        $companyId = (int) ($companyId ?? (auth()->check() ? auth()->user()->company_id : 1));

        return app(\App\Services\OrderNumberService::class)->nextWaiter($companyId);
    }

    public static function generateKioskoOrderNumber(?int $companyId = null): string
    {
        $companyId = (int) ($companyId ?? (auth()->check() ? auth()->user()->company_id : 1));

        $cashRegister = \App\Models\CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();

        if (!$cashRegister) {
            throw new \RuntimeException('No hay caja abierta');
        }

        return app(\App\Services\OrderNumberService::class)->nextKiosk($companyId);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'OPEN'              => 'ABIERTO',
            'SENT_TO_KITCHEN'   => 'ENVIADO A COCINA',
            'PREPARING'         => 'PREPARANDO',
            'READY'             => 'LISTO',
            'DELIVERED'         => 'ENTREGADO',
            'COMPLETED'         => 'COMPLETADO',
            'CANCELLED'         => 'ANULADO',
            default             => $this->status,
        };
    }
}
