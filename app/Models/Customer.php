<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'documento_tipo', 'documento_numero',
        'nombre', 'direccion', 'telefono', 'email', 'ubigeo', 'estado',
        'sunat_estado', 'sunat_condicion', 'sunat_bloqueado', 'sunat_checked_at',
    ];

    protected $casts = [
        'sunat_bloqueado' => 'boolean',
        'sunat_checked_at' => 'datetime',
    ];

    public function isSunatBlocked(): bool
    {
        if ((string) $this->documento_tipo !== '6') {
            return false;
        }

        return (bool) $this->sunat_bloqueado
            || strtoupper(trim((string) $this->sunat_estado)) !== 'ACTIVO';
    }

    public function canBeEdited(): bool
    {
        return $this->estado === 'ACTIVO' && !$this->isSunatBlocked();
    }

    public function canBeUsedForBilling(): bool
    {
        return $this->estado === 'ACTIVO' && !$this->isSunatBlocked();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}