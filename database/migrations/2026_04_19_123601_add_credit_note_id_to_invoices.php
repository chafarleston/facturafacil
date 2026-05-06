<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Guard against duplicate column if migration runs twice
        if (!\Schema::hasColumn('invoices', 'credit_note_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('credit_note_id')->nullable()->constrained('invoices')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\Schema::hasColumn('invoices', 'credit_note_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['credit_note_id']);
                $table->dropColumn('credit_note_id');
            });
        }
    }
};
