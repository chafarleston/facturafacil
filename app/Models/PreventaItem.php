<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'preventa_id',
        'product_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'igv_percent',
        'subtotal',
        'igv',
        'total',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'igv_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function preventa()
    {
        return $this->belongsTo(Preventa::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
