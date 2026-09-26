<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SunatAlert extends Model
{
    protected $fillable = [
        'tipo',
        'descripcion',
        'estado',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function resolve(): void
    {
        if ($this->estado === 'ACTIVO') {
            $this->update(['estado' => 'RESUELTO', 'resolved_at' => now()]);
        }
    }
}