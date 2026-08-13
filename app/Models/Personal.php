<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'company_id', 'dni', 'nombres', 'apellidos', 'cargo', 'sueldo', 'schedule_id', 'estado', 'suspendido',
        'foto', 'face_descriptor',
    ];

    protected $casts = [
        'sueldo' => 'decimal:2',
        'suspendido' => 'boolean',
        'face_descriptor' => 'array',
    ];

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function getHasFaceAttribute(): bool
    {
        return !empty($this->foto) && !empty($this->face_descriptor);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'personal_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
}
