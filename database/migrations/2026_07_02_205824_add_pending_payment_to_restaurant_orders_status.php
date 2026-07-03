<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE restaurant_orders MODIFY COLUMN status ENUM('OPEN','SENT_TO_KITCHEN','READY','DELIVERED','COMPLETED','CANCELLED','PENDING_PAYMENT') NOT NULL DEFAULT 'OPEN'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE restaurant_orders MODIFY COLUMN status ENUM('OPEN','SENT_TO_KITCHEN','READY','DELIVERED','COMPLETED','CANCELLED') NOT NULL DEFAULT 'OPEN'");
    }
};
