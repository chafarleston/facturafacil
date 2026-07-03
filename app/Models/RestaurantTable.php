<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'floor_id',
        'name',
        'capacity',
        'status',
        'color',
        'locked_by',
        'locked_at',
        'is_for_kiosko',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'locked_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(RestaurantOrder::class, 'table_id');
    }

    public function activeOrder()
    {
        return $this->orders()->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'OCCUPIED');
    }

    public function scopeExcludeKiosko($query)
    {
        return $query->where('is_for_kiosko', false);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLocked(): bool
    {
        return !is_null($this->locked_by);
    }

    public function isLockedBy(int $userId): bool
    {
        return $this->locked_by === $userId;
    }

    public function isLockExpired(): bool
    {
        if (!$this->locked_at) return true;
        return $this->locked_at->diffInMinutes(now()) >= 5;
    }

    public function lock(int $userId): void
    {
        $this->update([
            'locked_by' => $userId,
            'locked_at' => now(),
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }
}
