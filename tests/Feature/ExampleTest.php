<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemLock;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz redirige al login.
     *
     * Se desactiva SystemLock porque en el entorno de tests (SQLite :memory:)
     * no se ejecutan las migraciones (varias usan ALTER ... MODIFY ... ENUM, solo MySQL)
     * y el middleware consulta la tabla `settings`.
     */
    public function test_the_application_redirects_to_login(): void
    {
        $this->withoutMiddleware(SystemLock::class);

        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
