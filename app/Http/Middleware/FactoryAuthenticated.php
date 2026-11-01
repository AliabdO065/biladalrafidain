<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FactoryAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('factory')->check()) {
            return redirect()->route('factory.login');
        }

        return $next($request);
    }
}
