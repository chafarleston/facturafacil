<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class InventoryService
{
    private static int $suppressedStockMovements = 0;

    public static function isSuppressingStockMovement(): bool
    {
        return self::$suppressedStockMovements > 0;
    }

    public function move(
        Product $product,
        float $quantity,
        string $type,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
        $stockBefore = (float) $product->stock;
        $stockAfter = round($stockBefore + $quantity, 4);

        self::$suppressedStockMovements++;

        try {
            $product->stock = $stockAfter;
            $product->save();
        } finally {
            self::$suppressedStockMovements--;
        }

        return InventoryMovement::create([
            'company_id' => $product->company_id,
            'product_id' => $product->id,
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'quantity' => round($quantity, 4),
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
        ]);
    }

    public function sale(Product $product, float $quantity, ?Model $reference = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, -abs($quantity), 'sale', $reference, $notes);
    }

    public function purchase(Product $product, float $quantity, ?Model $reference = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, abs($quantity), 'purchase', $reference, $notes);
    }

    public function purchaseCancel(Product $product, float $quantity, ?Model $reference = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, -abs($quantity), 'purchase_cancel', $reference, $notes);
    }

    public function stockOutput(Product $product, float $quantity, ?Model $reference = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, -abs($quantity), 'stock_output', $reference, $notes);
    }

    public function stockOutputCancel(Product $product, float $quantity, ?Model $reference = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, abs($quantity), 'stock_output_cancel', $reference, $notes);
    }

    public function restoreSale(Invoice $invoice, ?Model $reference = null, string $notes = 'Devolución de venta'): int
    {
        // Evita duplicar la reposición de una misma venta.
        $alreadyRestored = InventoryMovement::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->where('type', 'sale_return')
            ->exists();

        if ($alreadyRestored) {
            return 0;
        }

        $items = $invoice->items()->with('product.components.component')->get();
        $restored = 0;

        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $quantity = (float) $item->cantidad;

            if ($product->is_composite) {
                foreach ($product->components as $component) {
                    if ($component->component) {
                        $this->move(
                            $component->component,
                            abs((float) $component->quantity * $quantity),
                            'sale_return',
                            $invoice,
                            $notes . ' - ' . $invoice->full_number
                        );
                        $restored++;
                    }
                }
            } else {
                $this->move(
                    $product,
                    abs($quantity),
                    'sale_return',
                    $invoice,
                    $notes . ' - ' . $invoice->full_number
                );
                $restored++;
            }
        }

        return $restored;
    }
}
