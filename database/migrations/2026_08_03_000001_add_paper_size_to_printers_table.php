<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->enum('paper_size', ['58mm', '80mm'])->default('80mm')->after('port');
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn('paper_size');
        });
    }
};
