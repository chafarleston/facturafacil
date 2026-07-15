<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'product_id', 'codigo', 'descripcion',
        'cantidad', 'umedida', 'precio_unitario', 'precio_venta',
        'igv', 'tipo_afectacion', 'igv_percent', 'detalle_consumo'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:4',
        'precio_venta' => 'decimal:2',
        'igv' => 'decimal:2',
        'igv_percent' => 'decimal:2',
        'detalle_consumo' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}