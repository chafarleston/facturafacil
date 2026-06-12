<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialDocumentItem extends Model
{
    protected $fillable = [
        'special_document_id', 'codigo', 'descripcion', 'cantidad', 'unidad',
    ];

    public $timestamps = false;

    public function document()
    {
        return $this->belongsTo(SpecialDocument::class);
    }
}
