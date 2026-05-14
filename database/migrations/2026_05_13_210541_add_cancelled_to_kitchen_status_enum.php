<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE restaurant_order_items MODIFY COLUMN kitchen_status ENUM('PENDING','SENT','READY','DELIVERED','CANCELLED') NOT NULL DEFAULT 'PENDING'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE restaurant_order_items MODIFY COLUMN kitchen_status ENUM('PENDING','SENT','READY','DELIVERED') NOT NULL DEFAULT 'PENDING'");
    }
};
