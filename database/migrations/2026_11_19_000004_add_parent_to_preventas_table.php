<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preventas', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('invoice_id')->constrained('preventas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('preventas', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
