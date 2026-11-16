<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $studentId = session('student_id', 1);

        // Fetch current student profile details
        $student = DB::table('students')->where('student_id', $studentId)->first();

        $subjects = DB::table('subjects')
            ->leftJoin('courses', 'subjects.course_id', '=', 'courses.course_id')
            ->where('courses.course_code', '=', $student->course)
            ->orWhere('courses.course_code', '=', 'ALL')
            ->orWhereNull('subjects.course_id')
            ->select('subjects.*', 'courses.course_code')
            ->orderBy('subjects.subject_code', 'asc')
            ->get();

        // 1. Fetch student POD slips (joining disciplinary_actions so violation status & remarks stay synced)
        $mySlips = DB::table('pod_slip')
            ->leftJoin('subjects', 'pod_slip.subject_id', '=', 'subjects.subject_id')
            ->leftJoin('violation_types', 'pod_slip.violation_type_id', '=', 'violation_types.violation_type_id')
            ->leftJoin('pod_attachment', 'pod_slip.pod_slip_id', '=', 'pod_attachment.pod_slip_id')
            ->leftJoin('disciplinary_actions', 'pod_slip.pod_slip_id', '=', 'disciplinary_actions.pod_slip_id')
            ->where('pod_slip.student_id', $studentId)
            ->select(
                'pod_slip.*',
                'subjects.subject_name',
                'subjects.subject_code',
                'violation_types.violation_name',
                'pod_attachment.file_path',
                'disciplinary_actions.action_id',
                'disciplinary_actions.action_type',
                'disciplinary_actions.status as action_status',
                'disciplinary_actions.description as action_description'
            )
            ->orderBy('pod_slip.pod_slip_id', 'desc')
            ->get();

        // 2. Overview Metrics (Only count student-submitted Attendance Slips for Total & Excused)
        $attendanceOnly = $mySlips->whereIn('slip_type', ['ABSENCE', 'TARDINESS']);
        $totalSlipsSubmitted = $attendanceOnly->count();
        $excusedCount = $attendanceOnly->whereIn('status', ['APPROVED', 'EXCUSED'])->count();

        $activeViolationsCount = DB::table('disciplinary_actions')
            ->join('pod_slip', 'disciplinary_actions.pod_slip_id', '=', 'pod_slip.pod_slip_id')
            ->where('pod_slip.student_id', $studentId)
            ->where('disciplinary_actions.status', 'ACTIVE')
            ->count();

        // 3. Chart Data Counts
        $absenceCount = $mySlips->where('slip_type', 'ABSENCE')->count();
        $tardinessCount = $mySlips->where('slip_type', 'TARDINESS')->count();
        $violationCount = $mySlips->where('slip_type', 'VIOLATION')->count();

        // 4. Fetch notifications
        $myNotifications = Notification::where('student_id', $studentId)
            ->where('message', 'not like', '[ADMIN]%')
            ->orderBy('notification_id', 'desc')
            ->get();

        $unreadCount = Notification::where('student_id', $studentId)
            ->where('message', 'not like', '[ADMIN]%')
            ->where('is_read', false)
            ->count();

        return view('student_dashboard', compact(
            'student', 
            'subjects', 
            'mySlips', 
            'myNotifications', 
            'unreadCount',
            'totalSlipsSubmitted',
            'excusedCount',
            'activeViolationsCount',
            'absenceCount',
            'tardinessCount',
            'violationCount'
        ));
    }

    public function markNotificationsRead()
    {
        $studentId = session('student_id', 1);
        
        Notification::where('student_id', $studentId)
            ->where('message', 'not like', '[ADMIN]%')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function storeSlip(Request $request)
    {
        $request->validate([
            'slip_type'      => 'required|in:ABSENT,LATE',
            'subject_id'     => 'required|exists:subjects,subject_id',
            'date_submitted' => 'required|date|before_or_equal:today',
            'description'    => 'nullable|string',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $studentId = session('student_id', 1);
        $mappedSlipType = $request->slip_type === 'LATE' ? 'TARDINESS' : 'ABSENCE';

        $podSlipId = DB::table('pod_slip')->insertGetId([
            'student_id'     => $studentId,
            'subject_id'     => $request->subject_id,
            'slip_type'      => $mappedSlipType,
            'description'    => $request->description ?? 'No reason provided',
            'date_submitted' => $request->date_submitted,
            'time_submitted' => now()->format('H:i:s'),
            'status'         => 'PENDING',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Log initial submission in pod_slip_history
        DB::table('pod_slip_history')->insert([
            'pod_slip_id'     => $podSlipId,
            'staff_admin_id'  => null,
            'event_type'      => 'CREATED',
            'previous_status' => 'NONE',
            'new_status'      => 'PENDING',
            'remarks'         => "Student submitted {$mappedSlipType} slip for {$request->date_submitted}.",
            'changed_at'      => now(),
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attachments', $fileName, 'public');

            DB::table('pod_attachment')->insert([
                'pod_slip_id' => $podSlipId,
                'file_name'   => $fileName,
                'file_type'   => $file->getClientOriginalExtension(),
                'file_size'   => $file->getSize(),
                'file_path'   => $filePath,
                'uploaded_at' => now(),
            ]);
        }

        $student = DB::table('students')->where('student_id', $studentId)->first();
        $slipRef = 'SLIP-' . str_pad($podSlipId, 4, '0', STR_PAD_LEFT);

        // 1. Student confirmation notification
        DB::table('notifications')->insert([
            'pod_slip_id'     => $podSlipId,
            'student_id'      => $studentId,
            'message'         => "Your {$request->slip_type} slip ({$slipRef}) for {$request->date_submitted} was submitted.",
            'is_read'         => false,
            'delivery_status' => 'SENT',
            'created_at'      => now(),
        ]);

        // 2. POD Admin incoming slip alert (picked up by NotificationController@index)
        DB::table('notifications')->insert([
            'pod_slip_id'     => $podSlipId,
            'student_id'      => $studentId,
            'message'         => "[ADMIN] {$student->name} ({$student->student_number}) submitted an {$mappedSlipType} slip ({$slipRef}) for {$request->date_submitted}.",
            'is_read'         => false,
            'delivery_status' => 'SENT',
            'created_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'POD Slip successfully created!');
    }

    public function updateProfile(Request $request)
    {
        $studentId = session('student_id', 1);
        $student = DB::table('students')->where('student_id', $studentId)->first();

        $validated = $request->validate([
            'email'            => 'nullable|email|max:150|unique:students,email,' . $studentId . ',student_id',
            'guardian_contact' => 'required|string|max:50',
            'current_password' => 'nullable|required_with:password|string',
            'password'         => 'nullable|string|min:6|confirmed',
        ], [
            'email.unique'                   => 'That email address is already linked to another student account.',
            'current_password.required_with' => 'Please enter your current password to set a new password.',
            'password.confirmed'             => 'New password and confirmation do not match.',
        ]);

        $updateData = [
            'email'            => $validated['email'] ?? $student->email,
            'guardian_contact' => $validated['guardian_contact'],
        ];

        if (!empty($request->password)) {
            if (!Hash::check($request->current_password, $student->password_hash)) {
                return redirect()->back()->withErrors(['current_password' => 'Your current password is incorrect.']);
            }
            $updateData['password_hash'] = Hash::make($request->password);
        }

        DB::table('students')->where('student_id', $studentId)->update($updateData);

        return redirect()->back()->with('success', 'Profile and security settings updated successfully!');
    }
}