<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'codigo', 'codigo_barras', 'descripcion', 'codigo_sunat',
        'umedida_codigo', 'precio', 'precio_minimo', 'tipo_afectacion',
        'igv_percent', 'estado', 'category_id', 'stock', 'kds_destination',
        'is_composite', 'precio_compra', 'imagen',
        'disposable_enabled', 'disposable_product_id',
    ];

    protected $casts = [
        'stock' => 'decimal:4',
        'disposable_enabled' => 'boolean',
    ];

    protected $appends = ['imagen_url'];

    protected static function booted(): void
    {
        static::created(function (Product $product) {
            $stock = (float) $product->stock;
            if (abs($stock) < 0.0001) {
                return;
            }

            static::recordStockAdjustment($product, 0, $stock, 'Stock inicial');
        });

        static::updated(function (Product $product) {
            if (InventoryService::isSuppressingStockMovement() || !$product->wasChanged('stock')) {
                return;
            }

            $before = (float) ($product->getOriginal('stock') ?? 0);
            $after = (float) $product->stock;

            if (abs($after - $before) < 0.0001) {
                return;
            }

            static::recordStockAdjustment($product, $before, $after, 'Ajuste manual de stock');
        });
    }

    private static function recordStockAdjustment(Product $product, float $before, float $after, string $notes): void
    {
        InventoryMovement::create([
            'company_id' => $product->company_id,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'adjustment',
            'quantity' => round($after - $before, 4),
            'stock_before' => $before,
            'stock_after' => $after,
            'notes' => $notes,
        ]);
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function disposableProduct()
    {
        return $this->belongsTo(Product::class, 'disposable_product_id');
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