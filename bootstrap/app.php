<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Configure OpenSSL 3.0+ to load legacy provider for SUNAT PKCS12 certificates
$opensslConf = getenv('OPENSSL_CONF') ?: 'C:\laragon\bin\php\php-8.4.5-Win32-vs17-x64\extras\ssl\openssl.cnf';
if (file_exists($opensslConf)) {
    putenv('OPENSSL_CONF=' . $opensslConf);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'theme' => \App\Http\Middleware\ThemeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();