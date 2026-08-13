<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'personal_id', 'fecha',
        'entrada_1', 'salida_1', 'entrada_2', 'salida_2',
        'tardanza_min', 'estado', 'descuento',
    ];

    protected $casts = [
        'fecha' => 'date',
        'descuento' => 'decimal:2',
        'entrada_1' => 'datetime',
        'salida_1' => 'datetime',
        'entrada_2' => 'datetime',
        'salida_2' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'PUNTUAL' => 'Puntual',
            'TARDANZA' => 'Tardanza',
            'FALTA' => 'Falta',
            'FALTA_GRAVE' => 'Falta Grave',
            'DESCANSO' => 'Descanso',
            default => $this->estado,
        };
    }
}
