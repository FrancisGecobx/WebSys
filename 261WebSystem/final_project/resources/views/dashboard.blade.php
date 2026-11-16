<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics - Prefect of Discipline</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="top-bar">
            <div class="page-header">
                <h2>System Analytics & Mining</h2>
                <p>Real-time disciplinary metrics and institutional insight.</p>
            </div>
            <div class="header-actions">
                <a href="/disciplinary-actions" class="btn-quick-add">+ Record Violation</a>
                <div class="user-profile">
                    <span>👤</span> {{ session('staff_name', 'Admin User') }}
                </div>
            </div>
        </div>

        @if($pendingReviews > 0)
            <div class="pending-alert-banner">
                <div class="pending-alert-content">
                    <span style="font-size: 1.75rem;">⚠️</span>
                    <div>
                        <div class="pending-alert-title">ATTENTION: Unreviewed POD Slips Pending!</div>
                        <div class="pending-alert-desc">
                            There {{ $pendingReviews === 1 ? 'is' : 'are' }} currently <strong>{{ $pendingReviews }} student {{ $pendingReviews === 1 ? 'slip' : 'slips' }}</strong> awaiting review.
                        </div>
                    </div>
                </div>
                <a href="/disciplinary-actions" class="btn-banner-action">Review Pending Slips Now →</a>
            </div>
        @endif

        <!-- METRICS GRID -->
        <div class="metrics-grid">
            <a href="/disciplinary-actions" class="metric-card">
                <div class="metric-title">Total POD Slips</div>
                <div class="metric-value">{{ $totalPodSlips }}</div>
                <div class="metric-sub">Issued system-wide</div>
            </a>
            <a href="/disciplinary-actions" class="metric-card warning">
                <div class="metric-title">Pending Reviews</div>
                <div class="metric-value" style="color: {{ $pendingReviews > 0 ? '#ea580c' : '#0f172a' }};">
                    {{ $pendingReviews }}
                </div>
                <div class="metric-sub {{ $pendingReviews > 0 ? 'alert' : '' }}">{{ $pendingReviews > 0 ? 'Action required' : 'All clear' }}</div>
            </a>
            <a href="/disciplinary-actions" class="metric-card">
                <div class="metric-title">Actions Executed</div>
                <div class="metric-value">{{ $actionsExecuted }}</div>
                <div class="metric-sub">Resolutions logged</div>
            </a>
            <a href="/students" class="metric-card">
                <div class="metric-title">Enrolled Students</div>
                <div class="metric-value">{{ $enrolledStudents }}</div>
                <div class="metric-sub">Active profiles</div>
            </a>
        </div>

        <!-- MIDDLE ROW: PENDING SLIPS & FREQUENT VIOLATIONS HORIZONTAL BAR CHART -->
        <div class="dashboard-row">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <span>📄</span> Pending ABSENT / LATE Slips
                    </h3>
                    <span class="badge" style="background: #fef3c7; color: #92400e;">
                        {{ count($pendingSlips) }} Pending
                    </span>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Slip Ref</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Subject / Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingSlips as $slip)
                            <tr>
                                <td><strong>SLIP-{{ str_pad($slip->pod_slip_id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    <strong>{{ $slip->student_name }}</strong><br>
                                    <small style="color: #64748b;">{{ $slip->student_number ?? 'N/A' }} ({{ $slip->course ?? 'BSIT' }})</small>
                                </td>
                                <td>
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1;">
                                        {{ $slip->slip_type ?? 'ABSENCE' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $slip->subject_name ?? 'General' }}<br>
                                    <small style="color: #64748b;">{{ $slip->date_submitted }}</small>
                                </td>
                                <td>
                                    <a href="/disciplinary-actions" style="background: #005a36; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.775rem; font-weight: 700;">
                                        Review Slip →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 28px;">
                                    ✓ No pending ABSENT or LATE slips requiring review.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- FREQUENT VIOLATIONS HORIZONTAL BAR CHART -->
            <div class="card">
                <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> Frequent Violations
                </h3>
                <div class="chart-container">
                    <canvas id="frequentViolationsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- RECENT DISCIPLINARY ACTIONS LOG -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <span>⚖️</span> Recent Disciplinary Actions Log
                </h3>
                <a href="/disciplinary-actions" style="font-size: 0.8rem; font-weight: 700; color: var(--primary-green); text-decoration: none;">
                    View All Cases →
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Case Ref</th>
                        <th>Student Name</th>
                        <th>Action Type</th>
                        <th>Description</th>
                        <th>Date Issued</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActions as $act)
                        <tr>
                            <td><strong>CASE-{{ str_pad($act->action_id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td><strong>{{ $act->student_name ?? 'Student' }}</strong></td>
                            <td>
                                <span class="badge {{ ($act->action_type ?? '') === 'SUSPENSION' ? 'badge-suspension' : 'badge-warning' }}">
                                    {{ $act->action_type }}
                                </span>
                            </td>
                            <td>
                                <div class="desc-clamp" title="{{ $act->description }}">
                                    {{ $act->description }}
                                </div>
                            </td>
                            <td>{{ $act->date_issued }}</td>
                            <td>
                                <span class="badge {{ ($act->status ?? '') === 'RESOLVED' ? 'badge-resolved' : 'badge-active' }}">
                                    {{ $act->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">No recent disciplinary actions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <footer class="student-footer">
                © {{ date('Y') }} Aldersgate College Inc. • Prefect of Discipline System — Developed by <strong>Francis Robert C. Gecobe</strong>
            </footer>
            
        </div>
    </main>

    <script>
        const violationData = @json($frequentViolations);
        const labels = violationData.map(item => item.category_name);
        const counts = violationData.map(item => item.total_occurrences);

        const ctx = document.getElementById('frequentViolationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['No Violations Recorded'],
                datasets: [{
                    label: 'Total Occurrences',
                    data: counts.length ? counts : [0],
                    backgroundColor: '#005a36',
                    hoverBackgroundColor: '#f2a900',
                    borderRadius: 6,
                    barThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const severity = violationData[index]?.severity;
                                return severity ? 'Severity: ' + severity.toUpperCase() : '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#f1f5f9' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, weight: '600' },
                            color: '#334155'
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>