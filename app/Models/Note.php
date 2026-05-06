<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'invoice_id', 'tipo_documento', 'serie', 'numero',
        'fecha_emision', 'tipo_nota', 'motivo', 'total', 'moneda',
        'codigo_hash', 'xml_path', 'sunat_estado', 'sunat_response'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}