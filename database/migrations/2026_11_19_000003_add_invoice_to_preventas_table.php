<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preventas', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('customer_id')->constrained('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('preventas', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
