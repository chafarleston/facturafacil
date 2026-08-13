<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY estado ENUM('PUNTUAL', 'TARDANZA', 'FALTA', 'FALTA_GRAVE', 'DESCANSO') NOT NULL DEFAULT 'PUNTUAL'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY estado ENUM('PUNTUAL', 'TARDANZA', 'FALTA', 'DESCANSO') NOT NULL DEFAULT 'PUNTUAL'");
    }
};
