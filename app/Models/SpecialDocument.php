<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialDocument extends Model
{
    protected $fillable = [
        'company_id', 'tipo_documento', 'serie', 'numero', 'full_number',
        'fecha_emision', 'moneda', 'total',
        'regimen', 'tasa', 'imp_retenido', 'imp_pagado', 'observacion',
        'dir_partida', 'dir_llegada',
        'cod_traslado', 'des_traslado', 'mod_traslado', 'fecha_traslado',
        'peso_total', 'und_peso_total', 'num_bultos', 'ubigeo_partida', 'ubigeo_llegada',
        'transportista_tipo_doc', 'transportista_num_doc', 'transportista_razon_social', 'transportista_nro_mtc',
        'vehiculo_placa', 'vehiculo_nro_circulacion', 'vehiculo_cod_emisor', 'vehiculo_nro_autorizacion',
        'chofer_tipo_doc', 'chofer_num_doc', 'chofer_nombres', 'chofer_apellidos', 'chofer_licencia',
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
