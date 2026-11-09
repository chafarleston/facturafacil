<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuxiliaryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVO');
    }

    public static function namesByIds(array $ids)
    {
        $ids = collect($ids)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return collect();
        }

        return static::whereIn('id', $ids)->pluck('name', 'id');
    }
}
