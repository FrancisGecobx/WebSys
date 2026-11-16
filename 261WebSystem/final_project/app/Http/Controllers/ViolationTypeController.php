<?php

namespace App\Http\Controllers;

use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViolationTypeController extends Controller
{
    public function index()
    {
        // Check for active staff/admin session
        if (!session()->has('staff_admin_id') && session('role') !== 'staff') {
            return redirect('/login')->with('error', 'Please log in as a Teacher / Admin.');
        }

        $types = DB::table('violation_types')->orderBy('violation_type_id', 'desc')->get();
        return view('violation_types', compact('types'));
    }

    public function store(Request $request)
    {
        // Check for active staff/admin session
        if (!session()->has('staff_admin_id') && session('role') !== 'staff') {
            return redirect('/login')->with('error', 'Please log in as a Teacher / Admin.');
        }

        $request->validate([
            'violation_name' => 'required|max:100',
            'severity_level' => 'required',
        ]);

        ViolationType::create([
            'violation_name' => $request->violation_name,
            'description'    => $request->description,
            'severity_level' => $request->severity_level,
            'created_at'     => now(),
        ]);

        return redirect('/violation-types')->with('success', 'Violation type added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'violation_name' => 'required|string|max:100',
            'severity_level' => 'required|in:Minor,Major,Severe',
            'description'    => 'nullable|string',
        ]);

        DB::table('violation_types')
            ->where('violation_type_id', $id)
            ->update([
                'violation_name' => $request->violation_name,
                'severity_level' => $request->severity_level,
                'description'    => $request->description,
            ]);

        return redirect('/violation-types')->with('success', 'Violation category updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $violation = DB::table('violation_types')->where('violation_type_id', $id)->first();
            DB::table('violation_types')->where('violation_type_id', $id)->delete();

            if ($violation && class_exists(\App\Http\Controllers\ActivityLogController::class)) {
                \App\Http\Controllers\ActivityLogController::log(
                    'Deleted Violation Type',
                    "Deleted violation category: {$violation->violation_name}"
                );
            }

            return redirect('/violation-types')->with('success', 'Violation category deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/violation-types')->withErrors([
                'error' => 'Cannot delete this violation category because it is linked to existing POD slips.'
            ]);
        }
    }
}