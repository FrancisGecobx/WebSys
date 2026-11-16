<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ActivityLogController;

class NotificationController extends Controller
{
   public function index()
    {
        // 1. Incoming Student Slip Alerts (Admin Inbox)
        $notifications = DB::table('notifications')
            ->leftJoin('students', 'notifications.student_id', '=', 'students.student_id')
            ->leftJoin('pod_slip', 'notifications.pod_slip_id', '=', 'pod_slip.pod_slip_id')
            ->select(
                'notifications.*',
                'students.name as student_name',
                'students.student_number',
                'pod_slip.status as slip_status'
            )
            ->where('notifications.message', 'like', '[ADMIN]%')
            ->orderBy('notifications.created_at', 'desc')
            ->get();

        // 2. Sent Direct Notice History (Admin Outbox)
        $sentNotices = DB::table('notifications')
            ->leftJoin('students', 'notifications.student_id', '=', 'students.student_id')
            ->select(
                'notifications.*',
                'students.name as student_name',
                'students.student_number',
                'students.email as student_email'
            )
            ->whereNull('notifications.pod_slip_id')
            ->where('notifications.message', 'not like', '[ADMIN]%')
            ->orderBy('notifications.created_at', 'desc')
            ->get();

        $students = DB::table('students')->orderBy('name')->get();$pendingSlipsCount = DB::table('pod_slip')->where('status', 'PENDING')->count();

        return view('notifications', compact('notifications', 'sentNotices', 'students', 'pendingSlipsCount'));
    }

    public function sendDirectMessage(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'subject'    => 'required|string|max:150',
            'message'    => 'required|string|max:1000',
            'send_email' => 'nullable|boolean',
        ]);

        $student = DB::table('students')->where('student_id', $request->student_id)->first();
        $deliveryStatus = 'SENT';
        $emailFeedback = '';

        // 1. Send via Gmail SMTP if requested and student has an email
        if ($request->boolean('send_email')) {
            if (!empty($student->email)) {
                try {
                    $emailBody = "Dear {$student->name} ({$student->student_number}),\n\n"
                        . "{$request->message}\n\n"
                        . "— Office of the Prefect of Discipline\n"
                        . "Aldersgate College Inc.";

                    Mail::raw($emailBody, function ($mail) use ($student, $request) {
                        $mail->to($student->email)
                             ->subject("[POD Notice] {$request->subject}");
                    });

                    $deliveryStatus = 'SENT + GMAIL';
                    $emailFeedback = " and emailed to {$student->email}";
                } catch (\Exception $e) {
                    $deliveryStatus = 'IN-APP ONLY (EMAIL FAILED)';
                    $emailFeedback = " (In-app sent, but Gmail failed: check SMTP credentials)";
                }
            } else {
                $emailFeedback = " (In-app sent; student has no email address on file)";
            }
        }

        // 2. Insert into student's in-app notifications (no [ADMIN] prefix so it appears on Student Bell)
        DB::table('notifications')->insert([
            'pod_slip_id'     => null,
            'student_id'      => $student->student_id,
            'message'         => "[POD Notice - {$request->subject}] {$request->message}",
            'is_read'         => 0,
            'delivery_status' => $deliveryStatus,
            'created_at'      => now(),
        ]);

        if (class_exists(ActivityLogController::class)) {
            ActivityLogController::log(
                'SEND_MESSAGE',
                "Sent direct POD message ('{$request->subject}') to {$student->name} ({$student->student_number}) [{$deliveryStatus}]."
            );
        }

        return redirect()->back()->with('success', "Message sent to {$student->name} in-portal{$emailFeedback}!");
    }

    public function markAsRead($id)
    {
        DB::table('notifications')
            ->where('notification_id', $id)
            ->update(['is_read' => 1]);

        return redirect()->back()->with('success', 'Notification marked as read!');
    }

    public function markAllRead()
    {
        DB::table('notifications')
            ->where('message', 'like', '[ADMIN]%')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return redirect()->back()->with('success', 'All admin notifications marked as read!');
    }
}