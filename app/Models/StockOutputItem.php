<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutputItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_output_id', 'product_id', 'cantidad', 'stock_antes', 'stock_despues',
    ];

    public function stockOutput()
    {
        return $this->belongsTo(StockOutput::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
