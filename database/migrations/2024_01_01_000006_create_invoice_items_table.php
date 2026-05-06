<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 4);
            $table->string('umedida', 3)->default('NIU');
            $table->decimal('precio_unitario', 12, 4);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('igv', 12, 2)->default(0);
            $table->string('tipo_afectacion', 3)->default('GRA');
            $table->decimal('igv_percent', 5, 2)->default(18);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};