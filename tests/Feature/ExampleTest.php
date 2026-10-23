<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemLock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // En SQLite :memory: no se ejecutan las migraciones; creamos solo
        // la tabla settings que consultan SystemLock y la ruta raíz.
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Web deshabilitada para este test + limpiar la caché de esa clave
        DB::table('settings')->updateOrInsert(
            ['key' => 'website_enabled'],
            ['value' => '0', 'created_at' => now(), 'updated_at' => now()]
        );
        Cache::forget('setting_website_enabled');
    }

    /**
     * La raíz redirige al login cuando la página web está deshabilitada.
     *
     * Se desactiva SystemLock porque el middleware consulta la tabla `settings`
     * (varias migraciones usan ALTER ... MODIFY ... ENUM, solo MySQL).
     */
    public function test_the_application_redirects_to_login(): void
    {
        $this->withoutMiddleware(SystemLock::class);

        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
