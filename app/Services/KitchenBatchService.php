<?php

namespace App\Services;

use App\Models\RestaurantKitchenBatch;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KitchenBatchService
{
    public function createFromItems(RestaurantOrder $order, Collection $items, ?int $userId = null): Collection
    {
        $items = $items
            ->filter(fn ($item) => $item instanceof RestaurantOrderItem)
            ->filter(fn ($item) => !$item->paid_invoice_id)
            ->filter(fn ($item) => $item->kitchen_status === RestaurantOrderItem::PENDING)
            ->values();

        if ($items->isEmpty()) {
            return collect();
        }

        $items->each(function (RestaurantOrderItem $item) {
            $product = $item->product;
            if ($product && $product->kds_destination) {
                $item->kds_destination = $product->kds_destination;
            }

            if (!$item->kds_destination) {
                $item->kds_destination = 'cocina';
            }
        });

        return DB::transaction(function () use ($order, $items, $userId) {
            $lockedOrder = RestaurantOrder::lockForUpdate()->findOrFail($order->id);
            $sentAt = now();
            $batches = collect();

            $groups = $items->groupBy(fn ($item) => $item->kds_destination ?: 'cocina');

            foreach ($groups as $destination => $group) {
                $nextNumber = (int) RestaurantKitchenBatch::where('restaurant_order_id', $lockedOrder->id)
                    ->where('kds_destination', $destination)
                    ->max('batch_number') + 1;

                $batch = RestaurantKitchenBatch::create([
                    'restaurant_order_id' => $lockedOrder->id,
                    'company_id' => $lockedOrder->company_id,
                    'kds_destination' => $destination,
                    'batch_number' => $nextNumber,
                    'status' => RestaurantKitchenBatch::STATUS_PENDING,
                    'sent_at' => $sentAt,
                    'sent_by_user_id' => $userId,
                ]);

                foreach ($group as $item) {
                    $item->kitchen_batch_id = $batch->id;
                    $item->kitchen_status = RestaurantOrderItem::SENT;
                    $item->sent_to_kitchen_at = $sentAt;
                    $item->save();
                }

                $batches->push($batch);
            }

            $this->syncOrderStatus($lockedOrder->fresh());

            return $batches;
        });
    }

    public function prepare(RestaurantKitchenBatch $batch): RestaurantKitchenBatch
    {
        return DB::transaction(function () use ($batch) {
            $batch = RestaurantKitchenBatch::lockForUpdate()->findOrFail($batch->id);

            if ($batch->status !== RestaurantKitchenBatch::STATUS_PENDING) {
                return $batch;
            }

            $batch->update([
                'status' => RestaurantKitchenBatch::STATUS_PREPARING,
                'preparing_started_at' => now(),
            ]);

            $this->syncOrderStatus($batch->order);

            return $batch->fresh();
        });
    }

    public function deliver(RestaurantKitchenBatch $batch): RestaurantKitchenBatch
    {
        return DB::transaction(function () use ($batch) {
            $batch = RestaurantKitchenBatch::lockForUpdate()->findOrFail($batch->id);

            if ($batch->status !== RestaurantKitchenBatch::STATUS_PREPARING) {
                return $batch;
            }

            $batch->update([
                'status' => RestaurantKitchenBatch::STATUS_DELIVERED,
                'finished_at' => now(),
            ]);

            RestaurantOrderItem::where('kitchen_batch_id', $batch->id)
                ->whereIn('kitchen_status', [RestaurantOrderItem::SENT, RestaurantOrderItem::READY])
                ->update(['kitchen_status' => RestaurantOrderItem::DELIVERED]);

            $this->syncOrderStatus($batch->order);

            return $batch->fresh();
        });
    }

    public function cancelBatchIfEmpty(RestaurantKitchenBatch $batch): void
    {
        $hasActiveItems = RestaurantOrderItem::where('kitchen_batch_id', $batch->id)
            ->where('kitchen_status', '!=', RestaurantOrderItem::CANCELLED)
            ->exists();

        if (!$hasActiveItems) {
            $batch->update([
                'status' => RestaurantKitchenBatch::STATUS_CANCELLED,
                'finished_at' => $batch->finished_at ?? now(),
            ]);
        }
    }

    public function syncOrderStatus(RestaurantOrder $order): void
    {
        $order = RestaurantOrder::with('kitchenBatches')->find($order->id);

        if (!$order || in_array($order->status, [RestaurantOrder::STATUS_COMPLETED, RestaurantOrder::STATUS_CANCELLED], true)) {
            return;
        }

        $batches = $order->kitchenBatches;
        $active = $batches->filter(fn ($batch) => $batch->isActive());

        if ($active->isEmpty()) {
            $hasRemainingItems = RestaurantOrderItem::where('restaurant_order_id', $order->id)
                ->whereNotIn('kitchen_status', [
                    RestaurantOrderItem::DELIVERED,
                    RestaurantOrderItem::CANCELLED,
                ])
                ->exists();

            if ($batches->isNotEmpty() && !$hasRemainingItems) {
                $order->status = RestaurantOrder::STATUS_DELIVERED;
                $order->finished_at = $order->finished_at ?? $batches->max('finished_at') ?? now();
                $order->save();
            }

            return;
        }

        $order->sent_to_kitchen_at = $active->min('sent_at') ?? $order->sent_to_kitchen_at;
        $order->finished_at = null;

        $preparing = $active->filter(
            fn ($batch) => $batch->status === RestaurantKitchenBatch::STATUS_PREPARING
        );

        if ($preparing->isNotEmpty()) {
            $order->status = RestaurantOrder::STATUS_PREPARING;
            $order->preparing_started_at = $preparing->min('preparing_started_at')
                ?? $order->preparing_started_at
                ?? now();
        } else {
            $order->status = RestaurantOrder::STATUS_SENT_TO_KITCHEN;
            $order->preparing_started_at = null;
        }

        $order->save();
    }
}
