<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

class DisciplinaryActionController extends Controller
{
    public function index()
    {
        // Students with prior violation count joined through pod_slip
        $students = DB::table('students')
            ->select(
                'students.*',
                DB::raw('(SELECT COUNT(*) FROM disciplinary_actions INNER JOIN pod_slip ON disciplinary_actions.pod_slip_id = pod_slip.pod_slip_id WHERE pod_slip.student_id = students.student_id) as violation_count')
            )
            ->orderBy('students.name')
            ->get();

        $violationTypes = DB::table('violation_types')->orderBy('violation_name')->get();

        // Direct violations joined through pod_slip and staff_admin
        $directViolations = DB::table('disciplinary_actions')
            ->leftJoin('pod_slip', 'disciplinary_actions.pod_slip_id', '=', 'pod_slip.pod_slip_id')
            ->leftJoin('students', 'pod_slip.student_id', '=', 'students.student_id')
            ->leftJoin('staff_admin', 'disciplinary_actions.staff_admin_id', '=', 'staff_admin.staff_admin_id')
            ->select(
                'disciplinary_actions.*',
                'disciplinary_actions.action_type as action_taken',
                'students.name as student_name',
                'students.student_number',
                'students.course',
                'staff_admin.name as issued_by'
            )
            ->orderBy('disciplinary_actions.date_issued', 'desc')
            ->orderBy('disciplinary_actions.action_id', 'desc')
            ->get();

        $pendingSlips = DB::table('pod_slip')
            ->leftJoin('students', 'pod_slip.student_id', '=', 'students.student_id')
            ->leftJoin('subjects', 'pod_slip.subject_id', '=', 'subjects.subject_id')
            ->leftJoin('pod_attachment', 'pod_slip.pod_slip_id', '=', 'pod_attachment.pod_slip_id')
            ->select(
                'pod_slip.*',
                'students.name as student_name',
                'students.student_number',
                'subjects.subject_name',
                'pod_attachment.file_path',
                'pod_attachment.file_name'
            )
            ->where('pod_slip.status', 'PENDING')
            ->orderBy('pod_slip.created_at', 'desc')
            ->get();

        // Audit trail directly from pod_slip_history (Attendance Slips only)
        $slipHistory = DB::table('pod_slip_history')
            ->leftJoin('pod_slip', 'pod_slip_history.pod_slip_id', '=', 'pod_slip.pod_slip_id')
            ->leftJoin('students', 'pod_slip.student_id', '=', 'students.student_id')
            ->leftJoin('subjects', 'pod_slip.subject_id', '=', 'subjects.subject_id')
            ->leftJoin('staff_admin', 'pod_slip_history.staff_admin_id', '=', 'staff_admin.staff_admin_id')
            ->leftJoin('pod_attachment', 'pod_slip.pod_slip_id', '=', 'pod_attachment.pod_slip_id')
            ->whereIn('pod_slip.slip_type', ['ABSENCE', 'TARDINESS'])
            ->where('pod_slip_history.previous_status', '!=', 'NONE') // <-- Hides initial pending submission logs
            ->select(
                'pod_slip_history.*',
                'pod_slip.slip_type',
                'students.name as student_name',
                'subjects.subject_name',
                'staff_admin.name as admin_name',
                'pod_attachment.file_path',
                'pod_attachment.file_name'
            )
            ->orderBy('pod_slip_history.changed_at', 'desc')
            ->orderBy('pod_slip_history.history_id', 'desc')
            ->get();

        return view('disciplinary_actions', compact(
            'students',
            'violationTypes',
            'directViolations',
            'pendingSlips',
            'slipHistory'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|exists:students,student_id',
            'violation_type' => 'required|string',
            'action_taken'   => 'required|string',
            'status'         => 'required|string',
            'description'    => 'nullable|string',
        ], [
            'student_id.required' => 'Please select a valid student from the suggestions or enter a valid Student ID.',
        ]);

        $adminId = session('staff_admin_id') ?? 1;
        $formattedNotes = "[{$request->violation_type}]" . ($request->description ? " " . $request->description : "");

        $violationRecord = DB::table('violation_types')
            ->where('violation_name', $request->violation_type)
            ->first();

        // 1. Insert into pod_slip
        $slipId = DB::table('pod_slip')->insertGetId([
            'student_id'        => $request->student_id,
            'violation_type_id' => $violationRecord->violation_type_id ?? null,
            'staff_admin_id'    => $adminId,
            'slip_type'         => 'VIOLATION',
            'description'       => $formattedNotes,
            'status'            => 'APPROVED',
            'date_submitted'    => now()->toDateString(),
            'time_submitted'    => now()->toTimeString(),
        ]);

        // 2. Insert into disciplinary_actions
        $actionId = DB::table('disciplinary_actions')->insertGetId([
            'pod_slip_id'    => $slipId,
            'staff_admin_id' => $adminId,
            'action_type'    => $request->action_taken,
            'description'    => $formattedNotes,
            'date_issued'    => now()->toDateString(),
            'status'         => $request->status,
            'created_at'     => now(),
        ]);

        // 3. Log in pod_slip_history
        DB::table('pod_slip_history')->insert([
            'pod_slip_id'     => $slipId,
            'staff_admin_id'  => $adminId,
            'event_type'      => 'VIOLATION_RECORDED',
            'previous_status' => 'NONE',
            'new_status'      => $request->status,
            'remarks'         => "{$request->action_taken}: {$formattedNotes}",
            'changed_at'      => now(),
        ]);

        // 4. Notify student
        DB::table('notifications')->insert([
            'pod_slip_id'     => $slipId,
            'student_id'      => $request->student_id,
            'message'         => "Disciplinary Action Issued (CASE-" . str_pad($actionId, 4, '0', STR_PAD_LEFT) . "): '{$request->action_taken}' for '{$request->violation_type}'. Please report to the Prefect of Discipline Office.",
            'is_read'         => 0,
            'delivery_status' => 'SENT',
            'created_at'      => now(),
        ]);

        if (class_exists(ActivityLogController::class)) {
            ActivityLogController::log(
                'RECORD_VIOLATION',
                "Recorded direct violation action #{$actionId} ({$request->action_taken}) for student ID #{$request->student_id}."
            );
        }

        return redirect()->back()->with('success', 'Violation action recorded and student notified!');
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution_remarks' => 'required|string|max:500',
        ]);

        $adminId = session('staff_admin_id') ?? 1;

        $action = DB::table('disciplinary_actions')
            ->join('pod_slip', 'disciplinary_actions.pod_slip_id', '=', 'pod_slip.pod_slip_id')
            ->where('disciplinary_actions.action_id', $id)
            ->select('disciplinary_actions.*', 'pod_slip.student_id', 'pod_slip.pod_slip_id')
            ->first();

        if ($action) {
            $dateCleared = now()->format('M d, Y');
            $updatedDescription = trim(($action->description ?? '') . " | [RESOLVED {$dateCleared}: {$request->resolution_remarks}]");

            DB::table('disciplinary_actions')
                ->where('action_id', $id)
                ->update([
                    'status'      => 'RESOLVED',
                    'description' => $updatedDescription,
                ]);

            DB::table('pod_slip_history')->insert([
                'pod_slip_id'     => $action->pod_slip_id,
                'staff_admin_id'  => $adminId,
                'event_type'      => 'CASE_RESOLVED',
                'previous_status' => $action->status ?? 'ACTIVE',
                'new_status'      => 'RESOLVED',
                'remarks'         => $request->resolution_remarks,
                'changed_at'      => now(),
            ]);

            DB::table('notifications')->insert([
                'pod_slip_id'     => $action->pod_slip_id,
                'student_id'      => $action->student_id,
                'message'         => "Case Resolved (CASE-" . str_pad($id, 4, '0', STR_PAD_LEFT) . "): Your '{$action->action_type}' record has been marked RESOLVED ({$request->resolution_remarks}). Your POD clearance is now updated.",
                'is_read'         => 0,
                'delivery_status' => 'SENT',
                'created_at'      => now(),
            ]);

            if (class_exists(ActivityLogController::class)) {
                ActivityLogController::log(
                    'RESOLVE_VIOLATION',
                    "Resolved violation action #{$id} with remarks: {$request->resolution_remarks}"
                );
            }
        }

        return redirect()->back()->with('success', 'Case marked as RESOLVED, clearance updated, and student notified!');
    }

    public function toggleStatus($id)
    {
        $action = DB::table('disciplinary_actions')->where('action_id', $id)->first();
        if ($action) {
            if (strtoupper($action->status) === 'RESOLVED') {
                return redirect()->back()->withErrors(['error' => 'Resolved disciplinary cases are permanently closed and cannot be reopened.']);
            }

            $newStatus = 'RESOLVED';
            DB::table('disciplinary_actions')->where('action_id', $id)->update([
                'status' => $newStatus,
            ]);

            if (class_exists(ActivityLogController::class)) {
                ActivityLogController::log(
                    'TOGGLE_STATUS',
                    "Resolved violation action #{$id} status to {$newStatus}."
                );
            }
        }
        return redirect()->back()->with('success', 'Violation status updated!');
    }

    public function approveSlip($id)
    {
        $slip = DB::table('pod_slip')->where('pod_slip_id', $id)->first();
        if (!$slip) {
            return back()->withErrors(['Slip record not found.']);
        }

        $adminId = session('staff_admin_id') ?? 1;
        $previousStatus = $slip->status ?? 'PENDING';

        DB::table('pod_slip')
            ->where('pod_slip_id', $id)
            ->update([
                'status'     => 'APPROVED',
                'updated_at' => now(),
            ]);
        // Mark the incoming [ADMIN] notification for this slip as read
        DB::table('notifications')
            ->where('pod_slip_id', $id)
            ->where('message', 'like', '[ADMIN]%')
            ->update(['is_read' => 1]);

        DB::table('pod_slip_history')->insert([
            'pod_slip_id'     => $id,
            'staff_admin_id'  => $adminId,
            'event_type'      => 'APPROVED',
            'previous_status' => $previousStatus,
            'new_status'      => 'APPROVED',
            'remarks'         => 'Slip approved and marked as excused by POD Admin.',
            'changed_at'      => now(),
        ]);

        DB::table('notifications')->insert([
            'student_id'      => $slip->student_id,
            'pod_slip_id'     => $id,
            'message'         => 'Your ' . strtoupper($slip->slip_type ?? 'POD') . ' slip (SLIP-' . str_pad($id, 4, '0', STR_PAD_LEFT) . ') has been APPROVED (EXCUSED).',
            'is_read'         => 0,
            'delivery_status' => 'SENT',
            'created_at'      => now(),
        ]);

        if (class_exists(ActivityLogController::class)) {
            ActivityLogController::log(
                'APPROVE_SLIP',
                "Approved POD slip #{$id} for student ID #{$slip->student_id}."
            );
        }

        return back()->with('success', 'Slip approved, logged in history, and student notified.');
    }

    public function rejectSlip(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $slip = DB::table('pod_slip')->where('pod_slip_id', $id)->first();
        if (!$slip) {
            return back()->withErrors(['Slip record not found.']);
        }

        $adminId = session('staff_admin_id') ?? 1;
        $previousStatus = $slip->status ?? 'PENDING';

        DB::table('pod_slip')
            ->where('pod_slip_id', $id)
            ->update([
                'status'     => 'REJECTED',
                'updated_at' => now(),
            ]);

        // Mark the incoming [ADMIN] notification for this slip as read
        DB::table('notifications')
            ->where('pod_slip_id', $id)
            ->where('message', 'like', '[ADMIN]%')
            ->update(['is_read' => 1]);

        DB::table('pod_slip_history')->insert([
            'pod_slip_id'     => $id,
            'staff_admin_id'  => $adminId,
            'event_type'      => 'REJECTED',
            'previous_status' => $previousStatus,
            'new_status'      => 'REJECTED',
            'remarks'         => $request->rejection_reason,
            'changed_at'      => now(),
        ]);

        DB::table('notifications')->insert([
            'student_id'      => $slip->student_id,
            'pod_slip_id'     => $id,
            'message'         => 'Your ' . strtoupper($slip->slip_type ?? 'POD') . ' slip (SLIP-' . str_pad($id, 4, '0', STR_PAD_LEFT) . ') was REJECTED. Reason: ' . $request->rejection_reason,
            'is_read'         => 0,
            'delivery_status' => 'SENT',
            'created_at'      => now(),
        ]);

        if (class_exists(ActivityLogController::class)) {
            ActivityLogController::log(
                'REJECT_SLIP',
                "Rejected POD slip #{$id} for student ID #{$slip->student_id}. Reason: {$request->rejection_reason}"
            );
        }

        return back()->with('success', 'Slip rejected, logged in history, and student notified.');
    }
}