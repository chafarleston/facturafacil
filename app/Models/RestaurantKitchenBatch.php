<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantKitchenBatch extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'PENDING';
    const STATUS_PREPARING = 'PREPARING';
    const STATUS_DELIVERED = 'DELIVERED';
    const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'restaurant_kitchen_batches';

    protected $fillable = [
        'restaurant_order_id',
        'company_id',
        'kds_destination',
        'batch_number',
        'status',
        'sent_at',
        'preparing_started_at',
        'finished_at',
        'sent_by_user_id',
    ];

    protected $casts = [
        'batch_number' => 'integer',
        'sent_at' => 'datetime',
        'preparing_started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class, 'kitchen_batch_id');
    }

    public function scopeForKds($query, string $destination)
    {
        return $query->where('kds_destination', $destination);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PREPARING]);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function getQueueSecondsAttribute(): ?int
    {
        if (!$this->sent_at || !$this->preparing_started_at) {
            return null;
        }

        return max(0, (int) $this->sent_at->diffInSeconds($this->preparing_started_at));
    }

    public function getPrepSecondsAttribute(): ?int
    {
        if (!$this->preparing_started_at || !$this->finished_at) {
            return null;
        }

        return max(0, (int) $this->preparing_started_at->diffInSeconds($this->finished_at));
    }

    public function getTotalSecondsAttribute(): ?int
    {
        if (!$this->sent_at || !$this->finished_at) {
            return null;
        }

        return max(0, (int) $this->sent_at->diffInSeconds($this->finished_at));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'PENDIENTE',
            self::STATUS_PREPARING => 'EN PREPARACIÓN',
            self::STATUS_DELIVERED => 'ENTREGADO',
            self::STATUS_CANCELLED => 'ANULADO',
            default => $this->status,
        };
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PREPARING], true);
    }
}
