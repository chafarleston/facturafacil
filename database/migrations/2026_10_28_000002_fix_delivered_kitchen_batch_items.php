<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('restaurant_order_items')
            ->whereIn('kitchen_status', ['PENDING', 'SENT', 'READY'])
            ->whereIn('kitchen_batch_id', function ($query) {
                $query->select('id')
                    ->from('restaurant_kitchen_batches')
                    ->where('status', 'DELIVERED');
            })
            ->update([
                'kitchen_status' => 'DELIVERED',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // El saneamiento de datos no se revierte automáticamente.
    }
};
