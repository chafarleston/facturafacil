<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'falta_threshold_min', 'descuento_falta_tipo', 'descuento_falta_valor',
        'falta_grave_threshold_min', 'suspension_graves_count',
        'reconocimiento_activo', 'reconocimiento_umbral', 'modo_marcacion', 'exito_segundos',
    ];

    protected $casts = [
        'descuento_falta_valor' => 'decimal:2',
        'reconocimiento_activo' => 'boolean',
        'reconocimiento_umbral' => 'decimal:4',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function forCompany(int $companyId): self
    {
        return static::firstOrCreate(
            ['company_id' => $companyId],
            [
                'falta_threshold_min' => 60,
                'descuento_falta_tipo' => 'FIJO',
                'descuento_falta_valor' => 0,
                'falta_grave_threshold_min' => 120,
                'suspension_graves_count' => 3,
                'reconocimiento_activo' => false,
                'reconocimiento_umbral' => 0.6,
                'modo_marcacion' => 'dni',
                'exito_segundos' => 20,
            ]
        );
    }
}
