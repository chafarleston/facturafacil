<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialDocument extends Model
{
    protected $fillable = [
        'company_id', 'tipo_documento', 'serie', 'numero', 'full_number',
        'fecha_emision', 'moneda', 'total',
        'sunat_estado', 'sunat_code', 'sunat_description',
        'xml_content', 'cdr_path', 'ticket',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function entity()
    {
        return $this->hasOne(SpecialDocumentEntity::class);
    }

    public function items()
    {
        return $this->hasMany(SpecialDocumentItem::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('tipo_documento', $type);
    }
}
