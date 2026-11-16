<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index()
    {
        // 1. Fetch courses for the dropdown
        $courses = DB::table('courses')->get();

        // 2. Fetch subjects joined with course information
        $subjects = DB::table('subjects')
            ->leftJoin('courses', 'subjects.course_id', '=', 'courses.course_id')
            ->select('subjects.*', 'courses.course_code', 'courses.course_name')
            ->orderBy('subjects.subject_id', 'desc')
            ->get();

        // 3. Return subjects view with BOTH $subjects and $courses
        return view('subjects', compact('subjects', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_code'    => 'required|string|max:50',
            'subject_name'    => 'required|string|max:255',
            'course_id'       => 'required|exists:courses,course_id',
            'schedule_days'   => 'required|string|max:100',
            'schedule_time'   => 'required|string|max:100',
            'schedule_days_2' => 'nullable|string|max:100',
            'schedule_time_2' => 'nullable|string|max:100',
        ]);

        DB::table('subjects')->insert([
            'subject_code'    => $request->subject_code,
            'subject_name'    => $request->subject_name,
            'course_id'       => $request->course_id,
            'schedule_days'   => $request->schedule_days,
            'schedule_time'   => $request->schedule_time,
            'schedule_days_2' => $request->schedule_days_2,
            'schedule_time_2' => $request->schedule_time_2,
        ]);

        return redirect()->back()->with('success', 'Subject record added successfully!');
    }
    public function destroy($id)
    {
        try {
            DB::table('subjects')->where('subject_id', $id)->delete();
            return redirect()->back()->with('success', 'Subject deleted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete this subject because it is linked to existing POD slip records.']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject_code'    => 'required|string|max:50',
            'subject_name'    => 'required|string|max:150',
            'course_id'       => 'required|integer',
            'schedule_days'   => 'required|string|max:100',
            'schedule_time'   => 'required|string|max:100',
            'schedule_days_2' => 'nullable|string|max:100',
            'schedule_time_2' => 'nullable|string|max:100',
        ]);

        DB::table('subjects')
            ->where('subject_id', $id)
            ->update([
                'subject_code'    => $request->subject_code,
                'subject_name'    => $request->subject_name,
                'course_id'       => $request->course_id,
                'schedule_days'   => $request->schedule_days,
                'schedule_time'   => $request->schedule_time,
                'schedule_days_2' => $request->schedule_days_2,
                'schedule_time_2' => $request->schedule_time_2,
            ]);

        return redirect('/subjects')->with('success', 'Subject record updated successfully.');
    }
}