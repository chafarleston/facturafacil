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
}
