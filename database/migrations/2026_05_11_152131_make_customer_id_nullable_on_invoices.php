<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->change();
        });
    }
};