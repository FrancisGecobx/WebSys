<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = DB::table('activity_logs')
            ->orderBy('log_id', 'desc')
            ->get();

        return view('activity_log', compact('logs'));
    }

    public static function log($action, $details)
    {
        DB::table('activity_logs')->insert([
            'user_name'  => session('staff_name', 'Admin User'),
            'action'     => $action,
            'details'    => $details,
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}