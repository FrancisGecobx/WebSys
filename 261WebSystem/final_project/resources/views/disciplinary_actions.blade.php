<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disciplinary Actions - Prefect of Discipline</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pod-styles.css') }}">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('ac_logo.png') }}" alt="Aldersgate College Logo">
            <div class="sidebar-brand-text">
                <h1>Aldersgate College</h1>
                <p>Prefect of Discipline</p>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li><a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard</a></li>
            <li><a href="/notifications" class="{{ request()->is('notifications') ? 'active' : '' }}"><span>🔔</span> Notifications</a></li>
            <li><a href="/disciplinary-actions" class="{{ request()->is('disciplinary-actions*') ? 'active' : '' }}"><span>⚖️</span> Disciplinary Actions</a></li>
            <li><a href="/students" class="{{ request()->is('students') ? 'active' : '' }}"><span>👨‍🎓</span> Manage Students</a></li>
            <li><a href="/subjects" class="{{ request()->is('subjects') ? 'active' : '' }}"><span>📚</span> Manage Subjects</a></li>
            <li><a href="/violation-types" class="{{ request()->is('violation-types') ? 'active' : '' }}"><span>⚠️</span> Violation Types</a></li>
            <li><a href="/activity-log" class="{{ request()->is('activity-log') ? 'active' : '' }}"><span>📋</span> Activity Log</a></li>
            <li><a href="/staff-admins" class="{{ request()->is('staff-admins*') ? 'active' : '' }}"><span>🛡️</span> Manage Admins</a></li>
        </ul>

        <div class="sidebar-footer">
            <div class="sidebar-credit">© {{ date('Y') }} Francis Robert C. Gecobe</div>
            <a href="/logout" class="btn-logout"><span>🚪</span> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h2>Disciplinary Actions Management</h2>
        </div>

        @if (session('success'))
            <div class="alert-success">✓ {{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <!-- TAB SWITCHER BUTTONS -->
        <div class="tab-switcher">
            <button class="tab-btn active" onclick="switchTab('direct-violations', this)">🚨 Direct Student Violation</button>
            <button class="tab-btn" onclick="switchTab('slip-reviews', this)">📄 Absent / Late Slip Review</button>
        </div>

        <!-- TAB 1: DIRECT STUDENT VIOLATIONS -->
        <div id="direct-violations" class="tab-content active">
            <!-- CREATE ACTION FORM -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom: 16px;">Record Direct Student Violation</h3>

                <!-- REPEAT-OFFENDER WARNING BANNER -->
                <div id="repeatOffenderAlert" class="repeat-warning"></div>

                <form method="POST" action="/disciplinary-actions" onsubmit="return validateStudentSelection()">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Select Student (Type ID or Name)</label>
                            <input type="text" id="student_search" list="studentOptions" placeholder="e.g. 23-0537 or Francis..." autocomplete="off" required>
                            <input type="hidden" name="student_id" id="selected_student_id" required>

                            <datalist id="studentOptions">
                                @foreach($students as $st)
                                    <option
                                        value="{{ $st->student_number }} - {{ $st->name }} ({{ $st->course }})"
                                        data-id="{{ $st->student_id }}"
                                        data-name="{{ $st->name }}"
                                        data-violations="{{ $st->violation_count ?? 0 }}"
                                    ></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label>Student Name</label>
                            <input type="text" id="auto_student_name" placeholder="Auto-populated" readonly style="background:#f1f5f9;">
                        </div>

                        <div class="form-group">
                            <label for="violation_type">Violation Type</label>
                            <select name="violation_type" id="violation_type" required>
                                <option value="" disabled selected>-- Select Violation Type --</option>
                                @foreach($violationTypes as $vt)
                                    @php
                                        $vName = $vt->violation_name ?? $vt->type_name ?? $vt->name ?? 'Violation';
                                        $vSev = $vt->severity_level ?? '';
                                    @endphp
                                    <option value="{{ $vName }}">{{ $vName }}{{ $vSev ? " ({$vSev})" : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="action_taken">Action Type (Sanction)</label>
                            <select name="action_taken" id="action_taken" required>
                                <option value="VERBAL / WRITTEN WARNING">VERBAL / WRITTEN WARNING</option>
                                <option value="PARENT / GUARDIAN CONFERENCE">PARENT / GUARDIAN CONFERENCE</option>
                                <option value="COMMUNITY SERVICE">COMMUNITY SERVICE</option>
                                <option value="PROBATION">PROBATION</option>
                                <option value="SUSPENSION">SUSPENSION</option>
                                <option value="DISMISSAL / EXPULSION">DISMISSAL / EXPULSION</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" required>
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="RESOLVED">RESOLVED</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="description">Violation Details / Sanction Terms</label>
                        <textarea name="description" id="description" rows="2" placeholder="Specify infraction details, community service hours, or suspension dates..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Record Violation Action</button>
                </form>
            </div>

            <!-- DIRECT VIOLATIONS LOG & PRINTABLE REPORT -->
            <div class="card" id="violationsReportArea">
                <div class="print-only-header">
                    <h3 style="color: #005a36; font-size: 1.15rem;">ALDERSGATE COLLEGE INC.</h3>
                    <p style="font-size: 0.82rem; color: #475569;">Office of the Prefect of Discipline • Official Disciplinary Actions Summary Report</p>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Generated on {{ now()->format('F d, Y h:i A') }}</p>
                </div>

                <div class="card-header-flex">
                    <h3 class="card-title" style="color: #991b1b;">Direct Student Violations Log</h3>
                    <div class="filter-bar no-print">
                        <select id="filterCourse" class="filter-select" onchange="filterViolationsTable()">
                            <option value="ALL">All Programs</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSA">BSA</option>
                        </select>
                        <select id="filterStatus" class="filter-select" onchange="filterViolationsTable()">
                            <option value="ALL">All Statuses</option>
                            <option value="ACTIVE">ACTIVE Only</option>
                            <option value="RESOLVED">RESOLVED Only</option>
                        </select>
                        <button type="button" class="btn-clearance" onclick="printViolationsReport()">🖨️ Print Summary Report</button>
                    </div>
                </div>

                <div style="width: 100%; overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Case Ref</th>
                                <th>Student</th>
                                <th>Action Taken</th>
                                <th>Violation & Resolution Notes</th>
                                <th>Date Issued</th>
                                <th>Issued By</th>
                                <th>Status</th>
                                <th class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($directViolations as $action)
                                @php
                                    $actUpper = strtoupper($action->action_taken ?? 'WARNING');
                                    $actBadge = str_contains($actUpper, 'SUSPENSION') || str_contains($actUpper, 'EXPULSION') || str_contains($actUpper, 'DISMISSAL')
                                        ? 'badge-suspension'
                                        : (str_contains($actUpper, 'COMMUNITY') || str_contains($actUpper, 'CONFERENCE') || str_contains($actUpper, 'PROBATION')
                                            ? 'badge-moderate'
                                            : 'badge-warning');
                                    $caseCode = 'CASE-' . str_pad($action->action_id, 4, '0', STR_PAD_LEFT);
                                @endphp
                                <tr class="violation-row" data-course="{{ strtoupper($action->course ?? '') }}" data-status="{{ strtoupper($action->status ?? '') }}">
                                    <td><strong>{{ $caseCode }}</strong></td>
                                    <td>
                                        <strong>{{ $action->student_name }}</strong><br>
                                        <span style="font-size: 0.75rem; color: #64748b;">{{ $action->student_number ?? '' }} • {{ $action->course ?? '' }}</span>
                                    </td>
                                    <td><span class="{{ $actBadge }}">{{ $actUpper }}</span></td>
                                    <td style="max-width: 280px;">{{ $action->description ?: '—' }}</td>
                                    <td>{{ $action->date_issued }}</td>
                                    <td>{{ $action->issued_by ?? 'POD Admin' }}</td>
                                    <td>
                                        <span class="{{ ($action->status ?? '') === 'RESOLVED' ? 'badge-resolved' : 'badge-active' }}">{{ $action->status }}</span>
                                    </td>
                                    <td class="no-print">
                                        @if(($action->status ?? '') === 'ACTIVE')
                                            <button
                                                type="button"
                                                class="btn-resolve"
                                                onclick="openResolveModal('{{ $action->action_id }}', '{{ addslashes($action->student_name) }}', '{{ addslashes($actUpper) }}')"
                                            >✓ Resolve Case</button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn-clearance"
                                                onclick="openClearanceModal(
                                                    '{{ $caseCode }}',
                                                    '{{ addslashes($action->student_name) }}',
                                                    '{{ addslashes($action->student_number ?? '') }}',
                                                    '{{ addslashes($action->course ?? '') }}',
                                                    '{{ addslashes($actUpper) }}',
                                                    '{{ addslashes($action->description ?? '') }}',
                                                    '{{ addslashes($action->date_issued) }}'
                                                )"
                                            >🖨️ Clearance Slip</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 20px;">No direct violations recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: SLIP REVIEWS -->
        <div id="slip-reviews" class="tab-content">
            <!-- PENDING SLIPS REVIEW CARD -->
            <div class="card">
                <h3 class="card-title" style="color: #005a36; margin-bottom: 18px;">Pending Student Slips (Awaiting Review)</h3>
                <div style="width: 100%; overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Slip Ref</th>
                                <th>Student Name</th>
                                <th>Slip Type</th>
                                <th>Subject</th>
                                <th>Reason / Notes</th>
                                <th>Attachment</th>
                                <th>Submitted Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingSlips as $slip)
                                @php
                                    $slipId = $slip->pod_slip_id ?? $slip->slip_id;
                                    $slipCode = 'SLIP-' . str_pad($slipId, 4, '0', STR_PAD_LEFT);
                                    $rawPath = $slip->file_path ?? $slip->attachment_path ?? null;
                                    $fileUrl = null;
                                    if (!empty($rawPath)) {
                                        $fileUrl = str_starts_with($rawPath, 'http') || str_starts_with($rawPath, '/storage/')
                                            ? asset($rawPath)
                                            : asset('storage/' . ltrim($rawPath, '/'));
                                    }
                                    $fileLabel = $slip->file_name ?? ($slipCode . ' Attachment');
                                @endphp
                                <tr>
                                    <td><strong>{{ $slipCode }}</strong></td>
                                    <td><strong>{{ $slip->student_name }}</strong></td>
                                    <td><span class="badge-pending">{{ strtoupper($slip->type ?? $slip->slip_type ?? 'SLIP') }}</span></td>
                                    <td>{{ $slip->subject_name ?? 'N/A' }}</td>
                                    <td>{{ $slip->description }}</td>
                                    <td>
                                        @if($fileUrl)
                                            <button
                                                type="button"
                                                class="btn-clearance"
                                                onclick="openAttachmentModal({{ json_encode($fileUrl) }}, {{ json_encode($fileLabel) }})"
                                            >📎 View File</button>
                                        @else
                                            <span style="color: #94a3b8; font-size: 0.78rem;">No file</span>
                                        @endif
                                    </td>
                                    <td>{{ $slip->date_submitted ?? $slip->created_at }}</td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <form method="POST" action="/pod-slips/{{ $slipId }}/approve" style="display: inline;">
                                                @csrf
                                                <button type="submit" style="background: #dcfce7; color: #166534; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Excused</button>
                                            </form>
                                            <button
                                                type="button"
                                                onclick="openNonExcusedSlipModal('{{ $slipId }}', '{{ $slipCode }}', '{{ addslashes($slip->student_name) }}')"
                                                style="background: #fee2e2; color: #991b1b; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer;"
                                            >Non-Excused</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 20px;">No pending slips awaiting review.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- POD SLIP AUDIT HISTORY LOG (FROM pod_slip_history) -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom: 18px;">Slip Status & Audit History Log</h3>
                <div style="width: 100%; overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>History ID</th>
                                <th>Slip Ref</th>
                                <th>Student Name</th>
                                <th>Type / Subject</th>
                                <th>Status Transition</th>
                                <th>Remarks</th>
                                <th>Reviewed By</th>
                                <th>Attachment</th>
                                <th>Changed At</th>
                                <th>Print Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slipHistory as $hist)
                                @php
                                    $hSlipCode = 'SLIP-' . str_pad($hist->pod_slip_id, 4, '0', STR_PAD_LEFT);
                                    $hRawPath = $hist->file_path ?? null;
                                    $hFileUrl = null;
                                    if (!empty($hRawPath)) {
                                        $hFileUrl = str_starts_with($hRawPath, 'http') || str_starts_with($hRawPath, '/storage/')
                                            ? asset($hRawPath)
                                            : asset('storage/' . ltrim($hRawPath, '/'));
                                    }
                                    $hFileLabel = $hist->file_name ?? ($hSlipCode . ' Attachment');
                                    $rawNewStat = strtoupper($hist->new_status ?? '');
                                    $displayStat = in_array($rawNewStat, ['APPROVED', 'EXCUSED'])
                                        ? 'EXCUSED'
                                        : (in_array($rawNewStat, ['REJECTED', 'NON-EXCUSED', 'UNEXCUSED']) ? 'NON-EXCUSED' : $rawNewStat);
                                @endphp
                                <tr>
                                    <td>#{{ $hist->history_id }}</td>
                                    <td><strong>{{ $hSlipCode }}</strong></td>
                                    <td><strong>{{ $hist->student_name ?? 'N/A' }}</strong></td>
                                    <td>
                                        <span class="badge-pending">{{ strtoupper($hist->slip_type ?? 'SLIP') }}</span><br>
                                        <span style="font-size: 0.75rem; color: #64748b;">{{ $hist->subject_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">{{ strtoupper($hist->previous_status ?? 'NONE') }}</span>
                                        <span style="margin: 0 4px;">→</span>
                                        <span class="{{ $displayStat === 'EXCUSED' ? 'badge-excused' : ($displayStat === 'NON-EXCUSED' ? 'badge-rejected' : 'badge-pending') }}">
                                            {{ $displayStat }}
                                        </span>
                                    </td>
                                    <td style="max-width: 240px;">{{ $hist->remarks ?: '—' }}</td>
                                    <td>{{ $hist->admin_name ?? 'POD Admin' }}</td>
                                    <td>
                                        @if($hFileUrl)
                                            <button
                                                type="button"
                                                class="btn-clearance"
                                                onclick="openAttachmentModal({{ json_encode($hFileUrl) }}, {{ json_encode($hFileLabel) }})"
                                            >📎 View File</button>
                                        @else
                                            <span style="color: #94a3b8; font-size: 0.78rem;">No file</span>
                                        @endif
                                    </td>
                                    <td>{{ $hist->changed_at }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn-clearance"
                                            onclick="openSlipPrintModal(
                                                {{ json_encode($hSlipCode) }},
                                                {{ json_encode($hist->student_name ?? 'N/A') }},
                                                {{ json_encode(strtoupper($hist->slip_type ?? 'ATTENDANCE SLIP')) }},
                                                {{ json_encode($hist->subject_name ?? 'N/A') }},
                                                {{ json_encode($displayStat) }},
                                                {{ json_encode($hist->remarks ?: 'No additional remarks.') }},
                                                {{ json_encode($hist->admin_name ?? 'POD Admin') }},
                                                {{ json_encode($hist->changed_at ?? '') }}
                                            )"
                                        >🖨️ Print Slip</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="text-align: center; color: #94a3b8; padding: 20px;">No slip history records logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- RESOLVE CASE MODAL -->
    <div class="modal-backdrop" id="resolveCaseModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Resolve Disciplinary Case</h3>
                <button type="button" class="modal-close" onclick="closeModal('resolveCaseModal')">&times;</button>
            </div>
            <form id="resolveCaseForm" method="POST">
                @csrf
                @method('PATCH')
                <p style="font-size: 0.85rem; color: #475569; margin-bottom: 14px;">
                    Clearing <strong id="resolve_student_label"></strong> (<span id="resolve_action_label"></span>). This will permanently close the case, update the student's POD clearance, and send an automated clearance notification.
                </p>
                <div class="form-group">
                    <label>Resolution Remarks / Sanction Fulfillment</label>
                    <textarea name="resolution_remarks" rows="3" required placeholder="e.g. Completed 10 hours community service; parent conference conducted on Nov 12, 2026..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('resolveCaseModal')">Cancel</button>
                    <button type="submit" class="btn-submit">Confirm Resolution & Notify</button>
                </div>
            </form>
        </div>
    </div>

    <!-- NON-EXCUSED SLIP MODAL (WITH REQUIRED REASON) -->
    <div class="modal-backdrop" id="nonExcusedSlipModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Mark Slip as Non-Excused</h3>
                <button type="button" class="modal-close" onclick="closeModal('nonExcusedSlipModal')">&times;</button>
            </div>
            <form id="nonExcusedSlipForm" method="POST">
                @csrf
                <p style="font-size: 0.85rem; color: #475569; margin-bottom: 14px;">
                    Provide a reason for marking <strong id="nonexcused_slip_code"></strong> submitted by <strong id="nonexcused_student_name"></strong> as <strong>Non-Excused</strong>. This will be sent to the student's portal notifications.
                </p>
                <div class="form-group">
                    <label>Reason for Non-Excused Status</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="e.g. Unreadable medical certificate attachment; please re-upload a clear copy or visit the POD office..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('nonExcusedSlipModal')">Cancel</button>
                    <button type="submit" class="btn-danger">Confirm Non-Excused & Notify</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PRINTABLE POD CLEARANCE SLIP MODAL (DIRECT VIOLATIONS) -->
    <div class="modal-backdrop" id="clearanceSlipModal">
        <div class="modal-box" style="max-width: 560px;">
            <div class="modal-header">
                <h3>POD Case Clearance Certificate</h3>
                <button type="button" class="modal-close" onclick="closeModal('clearanceSlipModal')">&times;</button>
            </div>

            <div class="clearance-paper" id="clearancePrintArea">
                <div class="clearance-header">
                    <h4>ALDERSGATE COLLEGE INC.</h4>
                    <p>Office of the Prefect of Discipline • Solano, Nueva Vizcaya</p>
                    <p style="font-weight: 700; color: #0f172a; margin-top: 6px;">CERTIFICATE OF DISCIPLINARY CASE CLEARANCE</p>
                </div>
                <div class="clearance-body">
                    <p><strong>Case Reference:</strong> <span id="clr_action_id"></span> &nbsp;|&nbsp; <strong>Date Issued:</strong> <span id="clr_date_issued"></span></p>
                    <p><strong>Student Name:</strong> <span id="clr_student_name"></span> (<span id="clr_student_number"></span> — <span id="clr_course"></span>)</p>
                    <p><strong>Sanction Recorded:</strong> <span id="clr_action_type"></span></p>
                    <p><strong>Case & Resolution Details:</strong><br><span id="clr_description" style="color: #334155;"></span></p>
                    <p style="margin-top: 12px; font-style: italic; color: #166534;">
                        This certifies that the student named above has fulfilled all required sanctions and is hereby CLEARED of this disciplinary record.
                    </p>
                </div>
                <div class="clearance-sig">
                    <div>
                        ___________________________<br>
                        Student Signature
                    </div>
                    <div style="text-align: right;">
                        ___________________________<br>
                        Prefect of Discipline Officer
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('clearanceSlipModal')">Close</button>
                <button type="button" class="btn-submit" onclick="printDocumentArea('clearancePrintArea')">🖨️ Print Clearance Slip</button>
            </div>
        </div>
    </div>

    <!-- PRINTABLE ABSENT / LATE ADMISSION SLIP MODAL -->
    <div class="modal-backdrop" id="slipPrintModal">
        <div class="modal-box" style="max-width: 560px;">
            <div class="modal-header">
                <h3>POD Absent / Late Admission Slip</h3>
                <button type="button" class="modal-close" onclick="closeModal('slipPrintModal')">&times;</button>
            </div>

            <div class="clearance-paper" id="slipPrintArea">
                <div class="clearance-header">
                    <h4>ALDERSGATE COLLEGE INC.</h4>
                    <p>Office of the Prefect of Discipline • Solano, Nueva Vizcaya</p>
                    <p style="font-weight: 700; color: #0f172a; margin-top: 6px;">OFFICIAL ABSENT / TARDINESS CLASS ADMISSION SLIP</p>
                </div>
                <div class="clearance-body">
                    <p><strong>Slip Reference:</strong> <span id="pr_slip_code"></span> &nbsp;|&nbsp; <strong>Date Reviewed:</strong> <span id="pr_slip_date"></span></p>
                    <p><strong>Student Name:</strong> <span id="pr_slip_student"></span></p>
                    <p><strong>Slip Category:</strong> <span id="pr_slip_type"></span> &nbsp;|&nbsp; <strong>Subject:</strong> <span id="pr_slip_subject"></span></p>
                    <p><strong>POD Determination:</strong> <strong id="pr_slip_status"></strong></p>
                    <p><strong>POD Remarks / Notes:</strong><br><span id="pr_slip_remarks" style="color: #334155;"></span></p>
                    <p id="pr_slip_footer_note" style="margin-top: 12px; font-style: italic; color: #166534;"></p>
                </div>
                <div class="clearance-sig">
                    <div>
                        ___________________________<br>
                        Subject Instructor Signature
                    </div>
                    <div style="text-align: right;">
                        <strong id="pr_slip_admin" style="display: block; color: #0f172a;"></strong>
                        ___________________________<br>
                        Prefect of Discipline Officer
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('slipPrintModal')">Close</button>
                <button type="button" class="btn-submit" onclick="printDocumentArea('slipPrintArea')">🖨️ Print Admission Slip</button>
            </div>
        </div>
    </div>

    <!-- ATTACHMENT PREVIEW MODAL -->
    <div class="modal-backdrop" id="attachmentPreviewModal">
        <div class="modal-box" style="max-width: 760px;">
            <div class="modal-header">
                <h3 id="att_modal_title">📎 Supporting Document</h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a id="att_download_link" href="#" target="_blank" style="background: var(--primary-green); color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; text-decoration: none;">
                        Open / Download ↗
                    </a>
                    <button type="button" class="modal-close" onclick="closeAttachmentModal()">&times;</button>
                </div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; max-height: 72vh; overflow: auto;">
                <img id="att_img_preview" src="" alt="Supporting Document" style="display: none; max-width: 100%; max-height: 66vh; margin: 0 auto; border-radius: 6px;">
                <iframe id="att_pdf_preview" src="" style="display: none; width: 100%; height: 66vh; border: none; border-radius: 6px;"></iframe>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeAttachmentModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }

        // Live Student ID / Name Datalist Matcher + Repeat-Offender Check
        document.getElementById('student_search').addEventListener('input', function () {
            const val = this.value.trim();
            const options = document.querySelectorAll('#studentOptions option');
            const banner = document.getElementById('repeatOffenderAlert');
            let matched = false;

            options.forEach(opt => {
                if (opt.value.toLowerCase() === val.toLowerCase() || opt.value.toLowerCase().startsWith(val.toLowerCase() + ' -')) {
                    document.getElementById('selected_student_id').value = opt.dataset.id;
                    document.getElementById('auto_student_name').value = opt.dataset.name;
                    matched = true;

                    const priorCount = parseInt(opt.dataset.violations || '0', 10);
                    if (priorCount >= 2) {
                        banner.className = 'repeat-warning high-risk';
                        banner.innerHTML = '🚨 <strong>Repeat Offender Alert:</strong> ' + opt.dataset.name + ' already has <strong>' + priorCount + ' recorded violations</strong> on file. Consider escalating sanction level.';
                        banner.style.display = 'block';
                    } else if (priorCount === 1) {
                        banner.className = 'repeat-warning';
                        banner.innerHTML = '⚠️ <strong>Prior Record Notice:</strong> ' + opt.dataset.name + ' has <strong>1 prior violation</strong> on file.';
                        banner.style.display = 'block';
                    } else {
                        banner.style.display = 'none';
                    }
                }
            });

            if (!matched) {
                document.getElementById('selected_student_id').value = '';
                document.getElementById('auto_student_name').value = '';
                banner.style.display = 'none';
            }
        });

        function validateStudentSelection() {
            if (!document.getElementById('selected_student_id').value) {
                alert('Please select a valid student from the dropdown suggestions or type an exact Student ID (e.g., 23-0537).');
                document.getElementById('student_search').focus();
                return false;
            }
            return true;
        }

        function filterViolationsTable() {
            const courseFilter = document.getElementById('filterCourse').value;
            const statusFilter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.violation-row');

            rows.forEach(row => {
                const rowCourse = row.dataset.course || '';
                const rowStatus = row.dataset.status || '';
                const matchCourse = (courseFilter === 'ALL' || rowCourse.includes(courseFilter));
                const matchStatus = (statusFilter === 'ALL' || rowStatus === statusFilter);
                row.style.display = (matchCourse && matchStatus) ? '' : 'none';
            });
        }

        function printDocumentArea(areaId) {
            const activeArea = document.getElementById('clearancePrintArea');
            const targetArea = document.getElementById(areaId);

            if (areaId !== 'clearancePrintArea' && activeArea && targetArea) {
                activeArea.id = 'clearancePrintArea_inactive';
                targetArea.id = 'clearancePrintArea';
            }

            document.body.classList.remove('printing-report');
            document.body.classList.add('printing-clearance');
            window.print();

            if (areaId !== 'clearancePrintArea' && targetArea) {
                targetArea.id = areaId;
                document.getElementById('clearancePrintArea_inactive').id = 'clearancePrintArea';
            }
        }

        function printViolationsReport() {
            document.body.classList.remove('printing-clearance');
            document.body.classList.add('printing-report');
            window.print();
        }

        function openResolveModal(actionId, studentName, actionType) {
            document.getElementById('resolveCaseForm').action = '/disciplinary-actions/' + actionId + '/resolve';
            document.getElementById('resolve_student_label').textContent = studentName;
            document.getElementById('resolve_action_label').textContent = actionType;
            document.getElementById('resolveCaseModal').classList.add('open');
        }

        function openNonExcusedSlipModal(slipId, slipCode, studentName) {
            document.getElementById('nonExcusedSlipForm').action = '/pod-slips/' + slipId + '/reject';
            document.getElementById('nonexcused_slip_code').textContent = slipCode;
            document.getElementById('nonexcused_student_name').textContent = studentName;
            document.getElementById('nonExcusedSlipModal').classList.add('open');
        }

        function openClearanceModal(id, name, number, course, actionType, description, dateIssued) {
            document.getElementById('clr_action_id').textContent = id;
            document.getElementById('clr_student_name').textContent = name;
            document.getElementById('clr_student_number').textContent = number;
            document.getElementById('clr_course').textContent = course;
            document.getElementById('clr_action_type').textContent = actionType;
            document.getElementById('clr_description').textContent = description;
            document.getElementById('clr_date_issued').textContent = dateIssued;
            document.getElementById('clearanceSlipModal').classList.add('open');
        }

        function openSlipPrintModal(slipCode, studentName, slipType, subjectName, status, remarks, adminName, reviewedDate) {
            document.getElementById('pr_slip_code').textContent = slipCode;
            document.getElementById('pr_slip_student').textContent = studentName;
            document.getElementById('pr_slip_type').textContent = slipType;
            document.getElementById('pr_slip_subject').textContent = subjectName;
            document.getElementById('pr_slip_remarks').textContent = remarks;
            document.getElementById('pr_slip_admin').textContent = adminName;
            document.getElementById('pr_slip_date').textContent = reviewedDate;

            const statusEl = document.getElementById('pr_slip_status');
            const footerNote = document.getElementById('pr_slip_footer_note');
            statusEl.textContent = status;

            if (status === 'EXCUSED') {
                statusEl.style.color = '#166534';
                footerNote.style.color = '#166534';
                footerNote.textContent = 'This slip has been verified by the Prefect of Discipline. The student is hereby EXCUSED and permitted to re-enter class.';
            } else {
                statusEl.style.color = '#991b1b';
                footerNote.style.color = '#991b1b';
                footerNote.textContent = 'This slip was marked NON-EXCUSED by the Prefect of Discipline. Please present this notice to your subject instructor.';
            }

            document.getElementById('slipPrintModal').classList.add('open');
        }

        function openAttachmentModal(fileUrl, fileName) {
            const img = document.getElementById('att_img_preview');
            const pdf = document.getElementById('att_pdf_preview');
            document.getElementById('att_modal_title').textContent = '📎 ' + fileName;
            document.getElementById('att_download_link').href = fileUrl;

            const cleanUrl = fileUrl.split('?')[0].toLowerCase();
            if (cleanUrl.endsWith('.pdf')) {
                img.style.display = 'none';
                pdf.src = fileUrl;
                pdf.style.display = 'block';
            } else {
                pdf.style.display = 'none';
                pdf.src = '';
                img.src = fileUrl;
                img.style.display = 'block';
            }

            document.getElementById('attachmentPreviewModal').classList.add('open');
        }

        function closeAttachmentModal() {
            document.getElementById('attachmentPreviewModal').classList.remove('open');
            document.getElementById('att_pdf_preview').src = '';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('open');
            if (modalId === 'attachmentPreviewModal') {
                document.getElementById('att_pdf_preview').src = '';
            }
        }

        window.addEventListener('click', function (e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.classList.remove('open');
                if (e.target.id === 'attachmentPreviewModal') {
                    document.getElementById('att_pdf_preview').src = '';
                }
            }
        });
    </script>
</body>
</html>