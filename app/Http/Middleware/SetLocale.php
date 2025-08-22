<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App as AppFacade;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale'));
        if (!in_array($locale, ['en','ar','ku'])) { $locale = 'en'; }
        AppFacade::setLocale($locale);
        return $next($request);
    }
}

