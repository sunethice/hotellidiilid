<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $local = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : 'en';     // set laravel localization
        app()->setLocale($local);
        return $next($request);
    }
}
