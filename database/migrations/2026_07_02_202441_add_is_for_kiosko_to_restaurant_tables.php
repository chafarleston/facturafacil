<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->boolean('is_for_kiosko')->default(false)->after('status');
        });

        $companies = DB::table('companies')->pluck('id');
        foreach ($companies as $companyId) {
            $exists = DB::table('restaurant_tables')
                ->where('company_id', $companyId)
                ->where('is_for_kiosko', true)
                ->exists();

            if (!$exists) {
                $floorId = DB::table('floors')
                    ->where('company_id', $companyId)
                    ->value('id');

                if ($floorId) {
                    DB::table('restaurant_tables')->insert([
                        'company_id' => $companyId,
                        'floor_id' => $floorId,
                        'name' => 'Kiosko',
                        'capacity' => 1,
                        'status' => 'AVAILABLE',
                        'is_for_kiosko' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('restaurant_tables')->where('is_for_kiosko', true)->delete();
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropColumn('is_for_kiosko');
        });
    }
};
