<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preventas', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            $table->string('document_type', 2)->nullable()->after('tipo');
            $table->string('payment_condition', 20)->nullable()->after('document_type');
            $table->unsignedSmallInteger('credit_days')->nullable()->after('payment_condition');
            $table->decimal('subtotal', 12, 2)->nullable()->after('monto');
            $table->decimal('igv', 12, 2)->nullable()->after('subtotal');
        });

        Schema::create('preventa_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventa_id')->constrained('preventas')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('igv_percent', 5, 2)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('igv', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventa_items');

        Schema::table('preventas', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'document_type',
                'payment_condition',
                'credit_days',
                'subtotal',
                'igv',
            ]);
        });
    }
};
