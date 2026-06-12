<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialDocumentEntity extends Model
{
    protected $fillable = [
        'special_document_id', 'tipo_doc', 'num_doc', 'razon_social', 'direccion',
    ];

    public $timestamps = false;

    public function document()
    {
        return $this->belongsTo(SpecialDocument::class);
    }
}
