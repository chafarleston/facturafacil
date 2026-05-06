<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'tipo_documento', 'serie', 'numero_actual', 'estado'
    ];

    protected $table = 'series';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getNextNumber(): int
    {
        return $this->numero_actual + 1;
    }

    public function incrementNumber(): void
    {
        $this->increment('numero_actual');
    }
}