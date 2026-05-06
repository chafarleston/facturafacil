<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'nombre', 'ruc', 'direccion', 'telefono', 'email', 'estado'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}