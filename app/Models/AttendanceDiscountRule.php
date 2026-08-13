<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDiscountRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'tardanza_min', 'tipo', 'valor',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function seedDefaults(int $companyId): void
    {
        $brackets = [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60];
        foreach ($brackets as $min) {
            static::firstOrCreate(
                ['company_id' => $companyId, 'tardanza_min' => $min],
                ['tipo' => 'FIJO', 'valor' => 0]
            );
        }
    }

    public static function forMinutes(int $companyId, int $minutes): ?self
    {
        return static::where('company_id', $companyId)
            ->where('tardanza_min', '<=', $minutes)
            ->orderByDesc('tardanza_min')
            ->first();
    }
}
