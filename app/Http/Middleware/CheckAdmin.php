<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('is_admin') || session('is_admin') !== true) {
            return redirect()->route('login')->with('error', 'Please login first');
        }
        return $next($request);
    }
}
