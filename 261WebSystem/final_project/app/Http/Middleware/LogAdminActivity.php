<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\ActivityLogController;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ignore GET/HEAD requests, static assets, and student portal routes
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $response;
        }

        if ($request->is('student/*', 'activity-log*', '_debugbar*', 'favicon.ico', 'build/*', 'storage/*')) {
            return $response;
        }

        // Skip routes that already log custom descriptive entries directly in DisciplinaryActionController
        if ($request->is('disciplinary-actions*', 'pod-slips*')) {
            return $response;
        }

        // Only log when an admin session is active (or during login/logout)
        if (!session()->has('staff_admin_id') && !$request->is('login*', 'logout*')) {
            return $response;
        }

        $method = $request->method();
        $path = '/' . ltrim($request->path(), '/');
        $segments = $request->segments();

        // 1. Determine Module Name
        $module = match (true) {
            $request->is('login*')           => 'AUTH',
            $request->is('logout*')          => 'AUTH',
            $request->is('students*')        => 'STUDENT',
            $request->is('courses*')         => 'COURSE',
            $request->is('subjects*')        => 'SUBJECT',
            $request->is('violation-types*') => 'VIOLATION_TYPE',
            $request->is('notifications*')   => 'NOTIFICATION',
            default                          => 'RECORD',
        };

        // 2. Determine Exact CRUD Operation
        $lastSegment = end($segments);
        $hasId = is_numeric($lastSegment) || count($segments) > 1;

        $crud = match (true) {
            $request->is('login*')                                => 'LOGIN',
            $request->is('logout*')                               => 'LOGOUT',
            str_contains($path, 'read')                           => 'MARK_READ',
            $method === 'DELETE' || str_contains($path, 'delete') => 'DELETE',
            in_array($method, ['PUT', 'PATCH']) || str_contains($path, 'update') || ($method === 'POST' && $hasId) => 'UPDATE',
            $method === 'POST'                                    => 'CREATE',
            default                                               => $method,
        };

        $action = ($module === 'AUTH') ? "AUTH_{$crud}" : "{$crud}_{$module}";

        // 3. Format Readable Details
        $verb = match ($crud) {
            'CREATE'    => 'Created new',
            'UPDATE'    => 'Updated',
            'DELETE'    => 'Deleted',
            'MARK_READ' => 'Marked as read',
            'LOGIN'     => 'Logged in to',
            'LOGOUT'    => 'Logged out of',
            default     => "Executed {$method} on",
        };

        $payload = collect($request->except(['_token', '_method', 'password', 'password_confirmation']))
            ->filter(fn($val) => is_scalar($val) && $val !== '')
            ->map(fn($val, $key) => "{$key}: {$val}")
            ->take(4)
            ->implode(', ');

        $target = is_numeric($lastSegment) ? " #{$lastSegment}" : '';
        $summary = $payload ? " — ({$payload})" : '';
        $details = "{$verb} " . strtolower(str_replace('_', ' ', $module)) . "{$target} via {$path}{$summary}";

        ActivityLogController::log($action, $details);

        return $response;
    }
}