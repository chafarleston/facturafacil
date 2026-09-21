<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class SystemLock
{
    public function handle(Request $request, Closure $next)
    {
        if (! Setting::isSystemLocked()) {
            return $next($request);
        }

        $excluded = ['login', 'logout', 'password/*', 'system-lock/toggle'];

        foreach ($excluded as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        return response()->view('errors.sistema_bloqueado', [], 503);
    }
}