<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'codigo', 'codigo_barras', 'descripcion', 'codigo_sunat',
        'umedida_codigo', 'precio', 'precio_minimo', 'tipo_afectacion',
        'igv_percent', 'estado', 'category_id', 'stock', 'kds_destination',
        'is_composite', 'precio_compra',
    ];

    protected $casts = [
        'stock' => 'decimal:4',
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

    public function components()
    {
        return $this->hasMany(ProductComponent::class, 'parent_product_id');
    }

    public function isComposite()
    {
        return $this->is_composite;
    }

    public function scopeSimple($query)
    {
        return $query->where('is_composite', false);
    }

    public function scopeComposite($query)
    {
        return $query->where('is_composite', true);
    }
}