<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'codigo', 'descripcion', 'codigo_sunat',
        'umedida_codigo', 'precio', 'precio_minimo', 'tipo_afectacion',
        'igv_percent', 'estado', 'category_id', 'stock'
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}