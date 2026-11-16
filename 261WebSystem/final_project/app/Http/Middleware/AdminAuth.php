<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('staff_admin_id')) {
            if (session()->has('student_id')) {
                return redirect('/student/dashboard')->withErrors(['Unauthorized access to admin portal.']);
            }
            return redirect('/login')->withErrors(['Please log in as an administrator first.']);
        }

        return $next($request);
    }
}