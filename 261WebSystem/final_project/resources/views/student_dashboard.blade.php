<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Portal - Prefect of Discipline</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pod-styles.css') }}">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="student-portal-body">

    <!-- NAVBAR -->
    <nav class="student-navbar">
        <div class="navbar-brand">
            <img src="{{ asset('ac_logo.png') }}" alt="Aldersgate College Logo">
            <div>
                <h1>Aldersgate College</h1>
                <p style="font-size: 0.75rem; color: rgba(255,255,255,0.8);">Student Discipline Portal</p>
            </div>
        </div>

        <div class="nav-right">
            <!-- NOTIFICATION BELL -->
            <button type="button" class="notif-btn" onclick="toggleNotifications()">
                🔔
                @if(isset($unreadCount) && $unreadCount > 0)
                    <span class="notif-badge" id="notif-badge-count">{{ $unreadCount }}</span>
                @endif
            </button>

            <!-- NOTIFICATION POPUP -->
            <div class="notif-dropdown" id="notif-dropdown">
                <div class="notif-header">Notifications Log</div>
                <div class="notif-item-list">
                    @forelse($myNotifications ?? [] as $n)
                        <div class="notif-item {{ !$n->is_read ? 'unread' : '' }}">
                            @if($n->pod_slip_id)
                                <strong>Slip #{{ $n->pod_slip_id }}:</strong>
                            @endif
                            {{ $n->message }}
                        </div>
                    @empty
                        <div style="padding: 16px; text-align: center; color: #94a3b8; font-size: 0.8rem;">No notifications.</div>
                    @endforelse
                </div>
            </div>

            <!-- PROFILE TRIGGER -->
            <button type="button" class="btn-profile-trigger" onclick="toggleProfileSidebar()">
                👤 My Profile
            </button>

            <!-- LOGOUT BUTTON -->
            <a href="/logout" class="student-btn-logout">Logout 🚪</a>
        </div>
    </nav>

    <!-- OVERLAY & PROFILE SIDEBAR -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleProfileSidebar()"></div>
    
    <div class="profile-sidebar" id="profile-sidebar">
        <div class="sidebar-header">
            <h3>Student Information</h3>
            <button type="button" class="close-btn" onclick="toggleProfileSidebar()">&times;</button>
        </div>

        <form method="POST" action="/student/profile/update">
            @csrf

            @if($errors->any())
                <div class="alert-error" style="font-size: 0.8rem; padding: 10px 12px; margin-bottom: 14px;">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif
            
            <div class="form-group">
                <label>Student ID Number (Locked)</label>
                <input type="text" value="{{ $student->student_number ?? 'N/A' }}" readonly>
            </div>

            <div class="form-group">
                <label>Full Name (Locked)</label>
                <input type="text" value="{{ $student->name ?? 'N/A' }}" readonly>
            </div>

            <div class="form-group">
                <label>Year Level & Course (Locked)</label>
                <input type="text" value="{{ ($student->course ?? 'N/A') . ' - ' . ($student->year_level ?? 'N/A') }}" readonly>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 18px 0;">

            <div class="form-group">
                <label for="prof_email">Gmail / Email Address (For Login & POD Notices)</label>
                <input type="email" id="prof_email" name="email" value="{{ $student->email ?? '' }}" placeholder="e.g. juan.delacruz@gmail.com">
            </div>

            <div class="form-group">
                <label for="prof_guardian">Guardian Contact Number</label>
                <input type="text" id="prof_guardian" name="guardian_contact" value="{{ $student->guardian_contact ?? '' }}" required>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 18px 0;">
            <p style="font-size: 0.78rem; font-weight: 700; color: #005a36; margin-bottom: 10px; text-transform: uppercase;">Change Password (Optional)</p>

            <div class="form-group">
                <label for="prof_current_password">Current Password</label>
                <input type="password" id="prof_current_password" name="current_password" placeholder="Required only if changing password">
            </div>

            <div class="form-group">
                <label for="prof_password">New Password</label>
                <input type="password" id="prof_password" name="password" placeholder="Minimum 6 characters">
            </div>

            <div class="form-group">
                <label for="prof_password_confirmation">Confirm New Password</label>
                <input type="password" id="prof_password_confirmation" name="password_confirmation" placeholder="Re-type new password">
            </div>

            <button type="submit" class="btn-submit" style="width: 100%; margin-top: 12px;">Save Profile Changes</button>
        </form>
    </div>

    <!-- MAIN BODY CONTENT -->
    <div class="student-container">
        
        @if(session('success'))
            <div class="alert-success" id="success-alert" style="transition: opacity 0.5s ease;">✓ {{ session('success') }}</div>
        @endif

        <!-- OVERVIEW METRIC CARDS -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-title">Total Submitted Slips</div>
                <div class="metric-value">{{ $totalSlipsSubmitted }}</div>
                <div class="metric-sub">Absences & Tardiness requests</div>
            </div>
            <div class="metric-card warning">
                <div class="metric-title">Excused Slips</div>
                <div class="metric-value">{{ $excusedCount }}</div>
                <div class="metric-sub">Excused by Prefect</div>
            </div>
            <div class="metric-card danger">
                <div class="metric-title">Active Violations</div>
                <div class="metric-value">{{ $activeViolationsCount }}</div>
                <div class="metric-sub">Active disciplinary actions</div>
            </div>
        </div>

        <!-- SUBMIT SLIP CARD -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 18px;">Submit POD Slip Request</h3>
            
            <form method="POST" action="/student/pod-slips" enctype="multipart/form-data">
                @csrf
                
                <label style="font-size: 0.825rem; font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Select Incident Type</label>
                <div class="selection-type">
                    <label class="type-option active-absent" id="label-absent">
                        <input type="radio" name="slip_type" value="ABSENT" checked onclick="toggleFlow('ABSENT')">
                        🔴 ABSENT
                    </label>
                    <label class="type-option" id="label-late">
                        <input type="radio" name="slip_type" value="LATE" onclick="toggleFlow('LATE')">
                        🟡 LATE
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="subject_id">What Subject?</label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="" disabled selected>-- Select Subject (Required) --</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->subject_id }}">{{ $sub->subject_code }} - {{ $sub->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_submitted">Select Date</label>
                        <input type="date" 
                               id="date_submitted" 
                               name="date_submitted" 
                               required 
                               value="{{ date('Y-m-d') }}" 
                               max="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group" id="attachment-group">
                        <label for="attachment">Attachment (Photo or PDF Excuse Slip)</label>
                        <input type="file" id="attachment" name="attachment" accept="image/*,.pdf">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label id="reason-label" for="description">Reason / Details</label>
                    <textarea id="description" name="description" rows="3" placeholder="Enter reason here..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">Enter & Submit Slip</button>
            </form>
        </div>

        <!-- SUMMARY BAR CHART CARD -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 12px;">Incident Breakdown Summary</h3>
            <div style="height: 220px; position: relative;">
                <canvas id="incidentChart"></canvas>
            </div>
        </div>

        <!-- TABLE 1: MY ATTENDANCE REQUESTS -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
                <h3 class="card-title">My Attendance Requests (Absence / Late)</h3>
                <input type="text" id="attendance-search" onkeyup="searchAttendanceTable()" placeholder="🔍 Search requests..." class="global-search" style="width: 220px;">
            </div>
            
            <div style="width: 100%; overflow-x: auto;">
                <table class="data-table" id="attendance-table">
                    <thead>
                        <tr>
                            <th>Slip ID</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>File</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $attendanceSlips = $mySlips->whereIn('slip_type', ['ABSENCE', 'TARDINESS']);
                        @endphp
                        @forelse($attendanceSlips as $slip)
                            <tr>
                                <td>#{{ $slip->pod_slip_id }}</td>
                                <td><strong>{{ $slip->slip_type }}</strong></td>
                                <td>{{ $slip->subject_code ?? 'N/A' }}</td>
                                <td>{{ $slip->date_submitted }}</td>
                                <td>{{ $slip->description }}</td>
                                <td>
                                    @if(!empty($slip->file_path))
                                        <a href="{{ asset('storage/' . $slip->file_path) }}" target="_blank" style="color: #005a36; text-decoration: underline; font-weight: 600;">📎 View Attachment</a>
                                    @else
                                        <span style="color: #94a3b8;">None</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusUpper = strtoupper($slip->status ?? 'PENDING');
                                        
                                        if (in_array($statusUpper, ['APPROVED', 'EXCUSED'])) {
                                            $statusBadge = 'badge-excused';
                                            $statusText = 'EXCUSED';
                                        } elseif (in_array($statusUpper, ['REJECTED', 'NON-EXCUSED', 'NOT EXCUSED', 'UNEXCUSED'])) {
                                            $statusBadge = 'badge-not-excused';
                                            $statusText = 'NON-EXCUSED';
                                        } else {
                                            $statusBadge = 'badge-pending';
                                            $statusText = 'PENDING';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">No attendance requests submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABLE 2: MY RECORDED VIOLATIONS LOG -->
        <div class="card">
            <h3 class="card-title" style="color: #991b1b; margin-bottom: 18px;">My Recorded Violations Log</h3>
            <div style="width: 100%; overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Action ID</th>
                            <th>Violation & Sanction</th>
                            <th>Date Logged</th>
                            <th>Details / Resolution Remarks</th>
                            <th>Disciplinary Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $violationSlips = $mySlips->where('slip_type', 'VIOLATION');
                        @endphp
                        @forelse($violationSlips as $slip)
                            @php
                                $finalStatus = strtoupper($slip->action_status ?? $slip->status ?? 'ACTIVE');
                                $finalRemarks = $slip->action_description ?? $slip->description;

                                if ($finalStatus === 'RESOLVED') {
                                    $statusBadge = 'badge-excused';
                                    $statusText = 'RESOLVED';
                                } else {
                                    $statusBadge = 'badge-not-excused';
                                    $statusText = 'ACTIVE';
                                }
                            @endphp
                            <tr>
                                <td>#{{ $slip->action_id ?? $slip->pod_slip_id }}</td>
                                <td>
                                    <strong style="color: #991b1b;">{{ $slip->violation_name ?? 'Direct Violation' }}</strong>
                                    @if(!empty($slip->action_type))
                                        <div style="font-size: 0.75rem; color: #475569; font-weight: 600; margin-top: 2px;">
                                            Sanction: {{ $slip->action_type }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $slip->date_submitted }}</td>
                                <td>{{ $finalRemarks }}</td>
                                <td>
                                    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">✓ No recorded violations on your profile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="student-footer">
    ©       {{ date('Y') }} Aldersgate College Inc. • Prefect of Discipline System — Developed by <strong>Francis Robert C. Gecobe</strong>
        </footer>
        
    </div>

    <script>
        // AUTO DISMISS FLASH ALERT
        const alertBox = document.getElementById('success-alert');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }, 3500);
        }

        // CLIENT-SIDE SEARCH FOR ATTENDANCE TABLE
        function searchAttendanceTable() {
            const input = document.getElementById('attendance-search').value.toLowerCase();
            const rows = document.querySelectorAll('#attendance-table tbody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        // BAR CHART INITIALIZATION
        const ctx = document.getElementById('incidentChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Absences', 'Tardiness (Late)', 'Direct Violations'],
                datasets: [{
                    label: 'Total Incidents Logged',
                    data: [{{ $absenceCount }}, {{ $tardinessCount }}, {{ $violationCount }}],
                    backgroundColor: ['#ef4444', '#f59e0b', '#005a36'],
                    borderRadius: 8,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        function toggleNotifications() {
            const dropdown = document.getElementById('notif-dropdown');
            dropdown.classList.toggle('show');

            const badge = document.getElementById('notif-badge-count');
            if (badge) {
                fetch('/student/notifications/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                }).then(() => {
                    badge.remove();
                });
            }
        }

        function toggleProfileSidebar() {
            document.getElementById('profile-sidebar').classList.toggle('open');
            document.getElementById('sidebar-overlay').classList.toggle('show');
        }

        function toggleFlow(type) {
            const absentLabel = document.getElementById('label-absent');
            const lateLabel = document.getElementById('label-late');
            const attachmentGroup = document.getElementById('attachment-group');
            const reasonLabel = document.getElementById('reason-label');

            if (type === 'ABSENT') {
                absentLabel.className = 'type-option active-absent';
                lateLabel.className = 'type-option';
                attachmentGroup.style.display = 'block';
                reasonLabel.innerText = 'Reason / Details (Optional)';
            } else {
                lateLabel.className = 'type-option active-late';
                absentLabel.className = 'type-option';
                attachmentGroup.style.display = 'none';
                reasonLabel.innerText = 'Reason Here (Required for Late)';
            }
        }

        @if($errors->any()) 
            document.getElementById('profile-sidebar').classList.add('open'); 
            document.getElementById('sidebar-overlay').classList.add('show'); 
        @endif
    </script>
</body>
</html>