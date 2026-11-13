<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('sunat_estado', 30)->nullable()->after('estado');
            $table->string('sunat_condicion', 50)->nullable()->after('sunat_estado');
            $table->boolean('sunat_bloqueado')->default(false)->after('sunat_condicion');
            $table->timestamp('sunat_checked_at')->nullable()->after('sunat_bloqueado');
            $table->index('sunat_bloqueado');
        });

        // Los RUC existentes quedan bloqueados hasta la primera sincronización con el padrón.
        DB::table('customers')
            ->where('documento_tipo', '6')
            ->update([
                'sunat_estado' => 'SIN VERIFICAR',
                'sunat_bloqueado' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['sunat_bloqueado']);
            $table->dropColumn([
                'sunat_estado',
                'sunat_condicion',
                'sunat_bloqueado',
                'sunat_checked_at',
            ]);
        });
    }
};
