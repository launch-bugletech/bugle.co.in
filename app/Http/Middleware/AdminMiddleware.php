<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
     public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role_id == 1 || Auth::user()->role_id == 3) {
            return $next($request);
        }

       return back();
    }
}