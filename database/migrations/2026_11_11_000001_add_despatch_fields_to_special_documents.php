<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'cod_traslado' => fn (Blueprint $table) => $table->string('cod_traslado', 2)->default('01')->after('dir_llegada'),
            'des_traslado' => fn (Blueprint $table) => $table->string('des_traslado', 200)->nullable()->after('cod_traslado'),
            'mod_traslado' => fn (Blueprint $table) => $table->string('mod_traslado', 2)->default('01')->after('des_traslado'),
            'fecha_traslado' => fn (Blueprint $table) => $table->date('fecha_traslado')->nullable()->after('mod_traslado'),
            'peso_total' => fn (Blueprint $table) => $table->decimal('peso_total', 12, 3)->default(0)->after('fecha_traslado'),
            'und_peso_total' => fn (Blueprint $table) => $table->string('und_peso_total', 5)->default('KGM')->after('peso_total'),
            'num_bultos' => fn (Blueprint $table) => $table->unsignedSmallInteger('num_bultos')->nullable()->after('und_peso_total'),
            'ubigeo_partida' => fn (Blueprint $table) => $table->string('ubigeo_partida', 6)->nullable()->after('num_bultos'),
            'ubigeo_llegada' => fn (Blueprint $table) => $table->string('ubigeo_llegada', 6)->nullable()->after('ubigeo_partida'),
            'transportista_tipo_doc' => fn (Blueprint $table) => $table->string('transportista_tipo_doc', 2)->nullable()->after('ubigeo_llegada'),
            'transportista_num_doc' => fn (Blueprint $table) => $table->string('transportista_num_doc', 15)->nullable()->after('transportista_tipo_doc'),
            'transportista_razon_social' => fn (Blueprint $table) => $table->string('transportista_razon_social', 255)->nullable()->after('transportista_num_doc'),
            'transportista_nro_mtc' => fn (Blueprint $table) => $table->string('transportista_nro_mtc', 50)->nullable()->after('transportista_razon_social'),
            'vehiculo_placa' => fn (Blueprint $table) => $table->string('vehiculo_placa', 20)->nullable()->after('transportista_nro_mtc'),
            'vehiculo_nro_circulacion' => fn (Blueprint $table) => $table->string('vehiculo_nro_circulacion', 50)->nullable()->after('vehiculo_placa'),
            'vehiculo_cod_emisor' => fn (Blueprint $table) => $table->string('vehiculo_cod_emisor', 50)->nullable()->after('vehiculo_nro_circulacion'),
            'vehiculo_nro_autorizacion' => fn (Blueprint $table) => $table->string('vehiculo_nro_autorizacion', 50)->nullable()->after('vehiculo_cod_emisor'),
            'chofer_tipo_doc' => fn (Blueprint $table) => $table->string('chofer_tipo_doc', 2)->nullable()->after('vehiculo_nro_autorizacion'),
            'chofer_num_doc' => fn (Blueprint $table) => $table->string('chofer_num_doc', 15)->nullable()->after('chofer_tipo_doc'),
            'chofer_nombres' => fn (Blueprint $table) => $table->string('chofer_nombres', 150)->nullable()->after('chofer_num_doc'),
            'chofer_apellidos' => fn (Blueprint $table) => $table->string('chofer_apellidos', 150)->nullable()->after('chofer_nombres'),
            'chofer_licencia' => fn (Blueprint $table) => $table->string('chofer_licencia', 50)->nullable()->after('chofer_apellidos'),
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('special_documents', $column)) {
                Schema::table('special_documents', $definition);
            }
        }
    }

    public function down(): void
    {
        $columns = [
            'chofer_licencia', 'chofer_apellidos', 'chofer_nombres', 'chofer_num_doc', 'chofer_tipo_doc',
            'vehiculo_nro_autorizacion', 'vehiculo_cod_emisor', 'vehiculo_nro_circulacion', 'vehiculo_placa',
            'transportista_nro_mtc', 'transportista_razon_social', 'transportista_num_doc', 'transportista_tipo_doc',
            'ubigeo_llegada', 'ubigeo_partida', 'num_bultos', 'und_peso_total', 'peso_total',
            'fecha_traslado', 'mod_traslado', 'des_traslado', 'cod_traslado',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('special_documents', $column)) {
                Schema::table('special_documents', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
