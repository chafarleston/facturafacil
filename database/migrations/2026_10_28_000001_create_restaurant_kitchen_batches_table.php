<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_kitchen_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_order_id')->constrained('restaurant_orders')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('kds_destination', 20)->default('cocina');
            $table->unsignedInteger('batch_number');
            $table->string('status', 20)->default('PENDING');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('preparing_started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['restaurant_order_id', 'kds_destination', 'batch_number'],
                'rk_batch_order_dest_num_unique'
            );
            $table->index(
                ['company_id', 'kds_destination', 'status', 'sent_at'],
                'rk_batch_company_dest_status_sent_idx'
            );
            $table->index(['status', 'finished_at'], 'rk_batch_status_finished_idx');
        });

        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->foreignId('kitchen_batch_id')
                ->nullable()
                ->after('restaurant_order_id')
                ->constrained('restaurant_kitchen_batches')
                ->nullOnDelete();
            $table->index('kitchen_batch_id');
        });

        $this->backfillBatches();
    }

    private function backfillBatches(): void
    {
        $items = DB::table('restaurant_order_items')
            ->whereNotNull('sent_to_kitchen_at')
            ->whereNull('kitchen_batch_id')
            ->orderBy('restaurant_order_id')
            ->orderBy('sent_to_kitchen_at')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $orders = DB::table('restaurant_orders')
            ->whereIn('id', $items->pluck('restaurant_order_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $groups = [];

        foreach ($items as $item) {
            $destination = $item->kds_destination ?: 'cocina';
            $key = $item->restaurant_order_id . '|' . $destination . '|' . $item->sent_to_kitchen_at;

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'order_id' => (int) $item->restaurant_order_id,
                    'destination' => $destination,
                    'sent_at' => $item->sent_to_kitchen_at,
                    'items' => [],
                ];
            }

            $groups[$key]['items'][] = $item;
        }

        uasort($groups, function ($a, $b) {
            return [$a['order_id'], $a['destination'], $a['sent_at']]
                <=> [$b['order_id'], $b['destination'], $b['sent_at']];
        });

        $batchNumbers = [];

        foreach ($groups as $group) {
            $order = $orders->get($group['order_id']);
            if (!$order) {
                continue;
            }

            $ids = collect($group['items'])->pluck('id')->all();
            $statuses = collect($group['items'])->pluck('kitchen_status')->filter()->values();

            $allCancelled = $statuses->isNotEmpty()
                && $statuses->every(fn ($status) => $status === 'CANCELLED');
            $allDeliveredOrCancelled = $statuses->isNotEmpty()
                && $statuses->every(fn ($status) => in_array($status, ['DELIVERED', 'CANCELLED'], true));
            $hasActive = $statuses->contains(
                fn ($status) => in_array($status, ['PENDING', 'SENT', 'READY'], true)
            );

            $sent = Carbon::parse($group['sent_at']);
            $orderPrep = $order->preparing_started_at ? Carbon::parse($order->preparing_started_at) : null;
            $orderFinished = $order->finished_at ? Carbon::parse($order->finished_at) : null;
            $maxItemUpdated = collect($group['items'])
                ->pluck('updated_at')
                ->filter()
                ->map(fn ($date) => Carbon::parse($date))
                ->max();
            $maxItemCancelled = collect($group['items'])
                ->pluck('cancelled_at')
                ->filter()
                ->map(fn ($date) => Carbon::parse($date))
                ->max();

            $status = 'PENDING';
            $preparingStartedAt = null;
            $finishedAt = null;

            if ($allCancelled || $order->status === 'CANCELLED') {
                $status = 'CANCELLED';
                $finishedAt = $maxItemCancelled ?? $maxItemUpdated ?? $sent->copy();
            } elseif ($allDeliveredOrCancelled || in_array($order->status, ['DELIVERED', 'COMPLETED'], true)) {
                $status = 'DELIVERED';
                $preparingStartedAt = $orderPrep && $orderPrep->greaterThan($sent)
                    ? $orderPrep->copy()
                    : $sent->copy();
                $finishedAt = $orderFinished ?? $maxItemUpdated ?? $sent->copy();

                if ($finishedAt->lessThan($preparingStartedAt)) {
                    $finishedAt = $preparingStartedAt->copy();
                }
            } elseif (in_array($order->status, ['PREPARING', 'READY'], true)) {
                if ($orderPrep && !$sent->greaterThan($orderPrep) && $hasActive) {
                    $status = 'PREPARING';
                    $preparingStartedAt = $orderPrep->copy();
                } else {
                    $status = $hasActive ? 'PENDING' : 'DELIVERED';

                    if ($status === 'DELIVERED') {
                        $preparingStartedAt = $orderPrep && $orderPrep->greaterThan($sent)
                            ? $orderPrep->copy()
                            : $sent->copy();
                        $finishedAt = $orderFinished ?? $maxItemUpdated ?? $sent->copy();
                    }
                }
            } else {
                $status = $hasActive ? 'PENDING' : 'DELIVERED';

                if ($status === 'DELIVERED') {
                    $preparingStartedAt = $orderPrep && $orderPrep->greaterThan($sent)
                        ? $orderPrep->copy()
                        : $sent->copy();
                    $finishedAt = $orderFinished ?? $maxItemUpdated ?? $sent->copy();
                }
            }

            $batchNumber = ($batchNumbers[$group['order_id']][$group['destination']] ?? 0) + 1;
            $batchNumbers[$group['order_id']][$group['destination']] = $batchNumber;

            $batchId = DB::table('restaurant_kitchen_batches')->insertGetId([
                'restaurant_order_id' => $group['order_id'],
                'company_id' => $order->company_id,
                'kds_destination' => $group['destination'],
                'batch_number' => $batchNumber,
                'status' => $status,
                'sent_at' => $sent,
                'preparing_started_at' => $preparingStartedAt,
                'finished_at' => $finishedAt,
                'sent_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('restaurant_order_items')
                ->whereIn('id', $ids)
                ->update(['kitchen_batch_id' => $batchId]);

            if ($status === 'DELIVERED') {
                DB::table('restaurant_order_items')
                    ->whereIn('id', $ids)
                    ->whereIn('kitchen_status', ['PENDING', 'SENT', 'READY'])
                    ->update([
                        'kitchen_status' => 'DELIVERED',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropForeign(['kitchen_batch_id']);
            $table->dropIndex(['kitchen_batch_id']);
            $table->dropColumn('kitchen_batch_id');
        });

        Schema::dropIfExists('restaurant_kitchen_batches');
    }
};
