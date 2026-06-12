<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summary_documents', function (Blueprint $table) {
            $table->string('correlativo', 30)->change();
        });
    }

    public function down(): void
    {
        Schema::table('summary_documents', function (Blueprint $table) {
            $table->string('correlativo', 10)->change();
        });
    }
};
