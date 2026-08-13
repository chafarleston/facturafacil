<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'tipo', 'entrada_1', 'salida_1',
        'entrada_2', 'salida_2', 'tolerancia_1', 'tolerancia_2',
        'dias_laborables', 'estado',
    ];

    protected $casts = [
        'dias_laborables' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class);
    }

    public function isWorkingDay(\DateTimeInterface $date): bool
    {
        $days = $this->dias_laborables;
        if (empty($days)) {
            return true;
        }
        return in_array((int) $date->format('N'), array_map('intval', (array) $days));
    }

    public function entryTimes(): array
    {
        return $this->tipo === 'dividido'
            ? [['time' => $this->entrada_1, 'event' => 'ENTRADA1'], ['time' => $this->entrada_2, 'event' => 'ENTRADA2']]
            : [['time' => $this->entrada_1, 'event' => 'ENTRADA1']];
    }

    public function eventSequence(): array
    {
        if ($this->tipo === 'dividido') {
            return [
                ['time' => $this->entrada_1, 'event' => 'ENTRADA1'],
                ['time' => $this->salida_1, 'event' => 'SALIDA1'],
                ['time' => $this->entrada_2, 'event' => 'ENTRADA2'],
                ['time' => $this->salida_2, 'event' => 'SALIDA2'],
            ];
        }
        return [
            ['time' => $this->entrada_1, 'event' => 'ENTRADA1'],
            ['time' => $this->salida_1, 'event' => 'SALIDA1'],
        ];
    }
}
