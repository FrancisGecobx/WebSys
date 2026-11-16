<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session()->has('staff_admin_id') && session('role') !== 'staff') {
            return redirect('/login')->with('error', 'Please log in as a Teacher / Admin.');
        }

        // 1. Metric Counts
        $totalPodSlips = DB::table('pod_slip')->count();
        
        $pendingReviews = DB::table('pod_slip')
            ->where('status', 'PENDING')
            ->count();
            
        $actionsExecuted = DB::table('disciplinary_actions')->count();
        $enrolledStudents = DB::table('students')->count();

        // 2. Pending ABSENT / LATE Slips
        $pendingSlips = DB::table('pod_slip')
            ->join('students', 'pod_slip.student_id', '=', 'students.student_id')
            ->leftJoin('subjects', 'pod_slip.subject_id', '=', 'subjects.subject_id')
            ->where('pod_slip.status', 'PENDING')
            ->whereIn('pod_slip.slip_type', ['ABSENCE', 'TARDINESS', 'ABSENT', 'LATE'])
            ->select(
                'pod_slip.*',
                'students.name as student_name',
                'students.student_number',
                'students.year_level as yr_level',
                'students.course',
                'subjects.subject_name'
            )
            ->orderBy('pod_slip.pod_slip_id', 'desc')
            ->limit(5)
            ->get();

        // 3. Most Frequent Violation Types
        $frequentViolations = DB::table('pod_slip')
            ->join('violation_types', 'pod_slip.violation_type_id', '=', 'violation_types.violation_type_id')
            ->select(
                'violation_types.violation_name as category_name',
                'violation_types.severity_level as severity',
                DB::raw('COUNT(pod_slip.pod_slip_id) as total_occurrences')
            )
            ->groupBy('violation_types.violation_type_id', 'violation_types.violation_name', 'violation_types.severity_level')
            ->orderByDesc('total_occurrences')
            ->limit(5)
            ->get();

        // 4. Recent Disciplinary Actions Log (Placed BEFORE return statement)
        $recentActions = DB::table('disciplinary_actions')
            ->leftJoin('pod_slip', 'disciplinary_actions.pod_slip_id', '=', 'pod_slip.pod_slip_id')
            ->leftJoin('students', 'pod_slip.student_id', '=', 'students.student_id')
            ->select(
                'disciplinary_actions.*',
                'students.name as student_name'
            )
            ->orderBy('disciplinary_actions.action_id', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalPodSlips',
            'pendingReviews',
            'actionsExecuted',
            'enrolledStudents',
            'pendingSlips',
            'frequentViolations',
            'recentActions'
        ));
    }
}