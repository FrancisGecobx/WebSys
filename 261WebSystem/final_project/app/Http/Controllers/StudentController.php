<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        // 1. Fetch course categories
        $courses = DB::table('courses')->orderBy('course_code', 'asc')->get();

        // 2. Fetch student records
        $students = DB::table('students')
        ->orderByRaw("FIELD(year_level, '1st Year', '2nd Year', '3rd Year', '4th Year') ASC")
        ->orderBy('student_number', 'asc')
        ->get();

        // 3. Group students by 'course' column (matches Navicat schema)
        $studentsByCourse = $students->groupBy('course');

        return view('students', compact('courses', 'students', 'studentsByCourse'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_number'   => 'required|string|max:50|unique:students,student_number',
            'name'             => 'required|string|max:150',
            'email'            => 'nullable|email|max:150|unique:students,email',
            'year_level'       => 'required|string|max:30',
            'course'           => 'required|string|max:50',
            'guardian_contact' => 'required|string|max:50',
            'password'         => 'required|string|min:6',
        ]);

        DB::table('students')->insert([
            'student_number'   => $request->student_number,
            'name'             => $request->name,
            'email'            => $request->email,
            'year_level'       => $request->year_level,
            'course'           => $request->course,
            'guardian_contact' => $request->guardian_contact,
            'password_hash'    => \Illuminate\Support\Facades\Hash::make($request->password),
            'created_at'       => now(),
        ]);

        return redirect()->back()->with('success', 'Student record added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'student_number'   => 'required|string|max:20',
            'name'             => 'required|string|max:100',
            'email'            => 'nullable|email|max:150',
            'course'           => 'required|string|max:50',
            'year_level'       => 'required|string|max:20',
            'guardian_contact' => 'nullable|string|max:50',
        ]);

        $data = [
            'student_number'   => $request->student_number,
            'name'             => $request->name,
            'email'            => $request->email,
            'course'           => $request->course,
            'year_level'       => $request->year_level,
            'guardian_contact' => $request->guardian_contact,
            'updated_at'       => now(),
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        \Illuminate\Support\Facades\DB::table('students')
            ->where('student_id', $id)
            ->update($data);

        return redirect('/students')->with('success', 'Student record updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('students')->where('student_id', $id)->delete();
        return redirect()->back()->with('success', 'Student record deleted successfully!');
    }
}