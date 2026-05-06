<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $theme = Session::get('theme', 'blue');
        view()->share('theme', $theme);
        
        return $next($request);
    }
}