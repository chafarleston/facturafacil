<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurant_tables', 'slug')) {
            Schema::table('restaurant_tables', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Rellena slugs de mesas existentes (Mesa 01 => mesa01)
        $tables = DB::table('restaurant_tables')->orderBy('id')->get();

        foreach ($tables as $row) {
            if (!empty($row->slug)) {
                continue;
            }

            $base = Str::slug($row->name, '');
            if ($base === '') {
                $base = 'mesa' . $row->id;
            }

            $slug = $base;
            $i = 2;
            while (DB::table('restaurant_tables')
                ->where('company_id', $row->company_id)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $base . '-' . $i++;
            }

            DB::table('restaurant_tables')->where('id', $row->id)->update(['slug' => $slug]);
        }

        // Índice único por empresa (si no existe ya)
        try {
            Schema::table('restaurant_tables', function (Blueprint $table) {
                $table->unique(['company_id', 'slug'], 'restaurant_tables_company_slug_unique');
            });
        } catch (\Throwable $e) {
            // Índice ya existente
        }
    }

    public function down(): void
    {
        try {
            Schema::table('restaurant_tables', function (Blueprint $table) {
                $table->dropUnique('restaurant_tables_company_slug_unique');
            });
        } catch (\Throwable $e) {
            // Sin índice
        }

        if (Schema::hasColumn('restaurant_tables', 'slug')) {
            Schema::table('restaurant_tables', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
