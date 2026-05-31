<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->enum('facturacion_mode', ['propio', 'api_externa'])
                  ->default('propio')->after('certificate');

            $table->string('pro51_server')->nullable()->after('facturacion_mode');
            $table->string('pro51_tenant')->nullable()->after('pro51_server');
            $table->string('pro51_email')->nullable()->after('pro51_tenant');
            $table->string('pro51_password')->nullable()->after('pro51_email');
            $table->string('pro51_establishment_code', 4)->default('0000')->after('pro51_password');
            $table->string('pro51_series_invoice', 4)->default('F001')->after('pro51_establishment_code');
            $table->string('pro51_series_receipt', 4)->default('B001')->after('pro51_series_invoice');
            $table->string('pro51_operation_type', 4)->default('0101')->after('pro51_series_receipt');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('pro51_codigo_interno', 50)->nullable()->after('codigo');
            $table->timestamp('pro51_synced_at')->nullable()->after('pro51_codigo_interno');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('pro51_external_id')->nullable()->unique()->after('codigo_hash');
            $table->json('pro51_response')->nullable()->after('pro51_external_id');
            $table->text('pro51_pdf_url')->nullable()->after('pro51_response');
            $table->text('pro51_xml_url')->nullable()->after('pro51_pdf_url');
            $table->text('pro51_cdr_url')->nullable()->after('pro51_xml_url');
            $table->timestamp('pro51_sent_at')->nullable()->after('pro51_cdr_url');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'facturacion_mode', 'pro51_server', 'pro51_tenant',
                'pro51_email', 'pro51_password', 'pro51_establishment_code',
                'pro51_series_invoice', 'pro51_series_receipt', 'pro51_operation_type'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['pro51_codigo_interno', 'pro51_synced_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'pro51_external_id', 'pro51_response', 'pro51_pdf_url',
                'pro51_xml_url', 'pro51_cdr_url', 'pro51_sent_at'
            ]);
        });
    }
};
