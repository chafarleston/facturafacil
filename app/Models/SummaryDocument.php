<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'fecha_emision', 'fecha_operacion', 'correlativo',
        'cantidad_documentos', 'ticket', 'sunat_estado',
        'sunat_response', 'sunat_fecha'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}