<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StudentAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('student_id')) {
            if (session()->has('staff_admin_id')) {
                return redirect('/dashboard')->withErrors(['Please use a student account to access the student portal.']);
            }
            return redirect('/login')->withErrors(['Please log in with your student account first.']);
        }

        return $next($request);
    }
}