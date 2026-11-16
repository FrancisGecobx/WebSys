<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Prefect of Discipline</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pod-styles.css') }}">
</head>
<body>

    <!-- Sidebar Navigation -->
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

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="header-bar">
            <h2>Notifications & Student Messaging</h2>
            <form method="POST" action="/notifications/mark-all-read">
                @csrf
                <button type="submit" class="btn-secondary">✓ Mark All Alerts as Read</button>
            </form>
        </div>

        @if (session('success'))
            <div class="alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <!-- COMPOSE MESSAGE TO STUDENT (IN-APP + GMAIL) -->
        <div class="card" style="margin-bottom: 24px;">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
                ✉️ Send Direct Notice to Student (System Portal + Gmail)
            </h3>

            <form method="POST" action="{{ route('notifications.send') }}">
                @csrf
                <input type="hidden" name="student_id" id="msg_student_id" required>

                <div class="compose-grid">
                    <div>
                        <label class="form-label">Select Student (Type ID or Name)</label>
                        <input type="text" id="msg_student_search" list="msgStudentList" class="form-control" placeholder="e.g. 23-0537 or Francis..." autocomplete="off" required>
                        <datalist id="msgStudentList">
                            @foreach($students ?? [] as $s)
                                <option value="{{ $s->student_number }} - {{ $s->name }}"
                                        data-id="{{ $s->student_id }}"
                                        data-name="{{ $s->name }}"
                                        data-email="{{ $s->email ?? 'No email on file' }}">
                                </option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="form-label">Recipient Email</label>
                        <input type="text" id="msg_student_email" class="form-control" placeholder="Auto-populated" readonly>
                    </div>

                    <div>
                        <label class="form-label">Subject / Notice Title</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Summons to POD Office / Clearance Follow-up" required>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label class="form-label">Message Details</label>
                    <textarea name="message" rows="2" class="form-control" placeholder="Write the message or instructions for the student..." required></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="send_email" value="1" checked style="width: 16px; height: 16px; accent-color: #005a36;">
                        Also send copy via Gmail to student's email address
                    </label>

                    <button type="submit" class="btn-primary">Send Notice</button>
                </div>
            </form>
        </div>

        <!-- SYSTEM NOTIFICATIONS TABLE CARD -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">
                    🔔 Incoming Student Slip Alerts
                    <span class="badge {{ $notifications->where('is_read', 0)->count() > 0 ? 'badge-unread' : 'badge-reviewed' }}" style="margin-left: 8px; vertical-align: middle;">
                        {{ $notifications->where('is_read', 0)->count() }} Unread
                    </span>
                </h3>
            </div>

            <!-- TOOLBAR & FILTERS -->
            <div class="toolbar">
                <div class="filter-tabs">
                    <button type="button" class="filter-btn active" onclick="filterNotifications('all', this)">All</button>
                    <button type="button" class="filter-btn" onclick="filterNotifications('unread', this)">Unread</button>
                    <button type="button" class="filter-btn" onclick="filterNotifications('read', this)">Read</button>
                </div>
                <div class="search-box">
                    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="🔍 Search student or alert...">
                </div>
            </div>

            <!-- NOTIFICATIONS TABLE -->
            <div style="width: 100%; overflow-x: auto;">
                <table class="data-table" id="notifTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ref Slip</th>
                            <th>Submitted By</th>
                            <th>Alert Details</th>
                            <th>Slip Status</th>
                            <th>Alert Status</th>
                            <th>Date Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $n)
                            <tr class="notif-row {{ !$n->is_read ? 'row-unread' : 'row-read' }}" style="{{ !$n->is_read ? 'background-color: #fffbeb;' : '' }}">
                                <td>#{{ $n->notification_id }}</td>
                                <td>
                                    @if($n->pod_slip_id)
                                        <span style="font-weight: 700; color: var(--primary-green);">Slip #{{ $n->pod_slip_id }}</span>
                                    @else
                                        <span style="color: #94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $n->student_name ?? 'Student #' . $n->student_id }}</strong>
                                    @if(!empty($n->student_number))
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ $n->student_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;">
                                        {{ trim(str_replace('[ADMIN]', '', $n->message)) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ ($n->slip_status ?? 'PENDING') === 'PENDING' ? 'badge-unread' : 'badge-read' }}">
                                        {{ $n->slip_status ?? 'PENDING' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $n->is_read ? 'badge-read' : 'badge-unread' }}">
                                        {{ $n->is_read ? 'READ' : 'UNREAD' }}
                                    </span>
                                </td>
                                <td>{{ $n->created_at }}</td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        @if(($n->slip_status ?? 'PENDING') === 'PENDING')
                                            <a href="/disciplinary-actions" class="btn-action" style="text-decoration: none; background: #dcfce7; color: #166534; border-color: #86efac;">
                                                Review Slip →
                                            </a>
                                        @else
                                            <span class="badge badge-reviewed">
                                                ✓ Reviewed
                                            </span>
                                        @endif

                                        @if(!$n->is_read)
                                            <form method="POST" action="/notifications/{{ $n->notification_id }}/read" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action">Mark Read</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">No student slip alerts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SENT DIRECT NOTICE HISTORY CARD -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">
                    📤 Sent Direct Notice History
                </h3>
                <div class="search-box">
                    <input type="text" id="sentSearchInput" onkeyup="searchSentTable()" placeholder="🔍 Search sent notices...">
                </div>
            </div>

            <div style="width: 100%; overflow-x: auto;">
                <table class="data-table" id="sentNotifTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Recipient Student</th>
                            <th>Message Preview</th>
                            <th>Delivery</th>
                            <th>Student Read</th>
                            <th>Date Sent</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sentNotices ?? [] as $notice)
                            <tr class="sent-row">
                                <td>#{{ $notice->notification_id }}</td>
                                <td>
                                    <strong>{{ $notice->student_name ?? 'Student #' . $notice->student_id }}</strong>
                                    <div style="font-size: 0.75rem; color: #64748b;">
                                        {{ $notice->student_number ?? '' }}{{ !empty($notice->student_email) ? ' • ' . $notice->student_email : '' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="msg-preview" title="{{ $notice->message }}">
                                        {{ $notice->message }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-sent">{{ $notice->delivery_status ?? 'SENT' }}</span>
                                </td>
                                <td>
                                    @if($notice->is_read)
                                        <span class="badge badge-read">✓ READ</span>
                                    @else
                                        <span class="badge badge-unread">UNREAD</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($notice->created_at)->format('M d, Y h:i A') }}</td>
                                <td>
                                    <button type="button"
                                        class="btn-action"
                                        style="background: #dcfce7; color: #166534; border-color: #86efac;"
                                        onclick="openSentMessageModal(
                                            {{ json_encode(($notice->student_name ?? 'Student') . (!empty($notice->student_number) ? ' (' . $notice->student_number . ')' : '')) }},
                                            {{ json_encode($notice->student_email ?? 'No email on file') }},
                                            {{ json_encode(\Carbon\Carbon::parse($notice->created_at)->format('M d, Y h:i A')) }},
                                            {{ json_encode($notice->delivery_status ?? 'SENT') }},
                                            {{ json_encode($notice->is_read ? 'Read by student' : 'Not yet read by student') }},
                                            {{ json_encode($notice->message) }}
                                        )">
                                        View Message
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">No direct notices sent yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL TO INSPECT FULL SENT MESSAGE -->
    <div id="sentMessageModal" onclick="if(event.target === this) closeSentMessageModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; width: 100%; max-width: 540px; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 35px rgba(0,0,0,0.2); border-top: 4px solid #f2a900;">
            <div style="background: #005a36; color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="font-size: 0.95rem; font-weight: 700;">📩 Sent Direct Notice Details</h4>
                <button type="button" onclick="closeSentMessageModal()" style="background: transparent; border: none; color: #fff; font-size: 1.3rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <div style="padding: 20px; font-size: 0.875rem; color: #334155;">
                <div style="margin-bottom: 8px;">
                    <strong style="color: #005a36;">To:</strong> <span id="modalRecipient"></span>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong style="color: #005a36;">Email:</strong> <span id="modalEmail"></span>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong style="color: #005a36;">Date Sent:</strong> <span id="modalDate"></span>
                </div>
                <div style="margin-bottom: 14px;">
                    <strong style="color: #005a36;">Status:</strong> <span id="modalStatus"></span>
                </div>

                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Full Message Sent</label>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; white-space: pre-wrap; line-height: 1.6; color: #1e293b; max-height: 280px; overflow-y: auto;" id="modalBody"></div>

                <div style="text-align: right; margin-top: 18px;">
                    <button type="button" onclick="closeSentMessageModal()" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterNotifications(filter, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const rows = document.querySelectorAll('.notif-row');
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'unread') {
                    row.style.display = row.classList.contains('row-unread') ? '' : 'none';
                } else if (filter === 'read') {
                    row.style.display = row.classList.contains('row-read') ? '' : 'none';
                }
            });
        }

        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.notif-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        function searchSentTable() {
            const input = document.getElementById('sentSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.sent-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        function openSentMessageModal(recipient, email, dateSent, deliveryStatus, readStatus, messageText) {
            document.getElementById('modalRecipient').innerText = recipient;
            document.getElementById('modalEmail').innerText = email;
            document.getElementById('modalDate').innerText = dateSent;
            document.getElementById('modalStatus').innerText = deliveryStatus + ' • ' + readStatus;
            document.getElementById('modalBody').innerText = messageText;
            document.getElementById('sentMessageModal').style.display = 'flex';
        }

        function closeSentMessageModal() {
            document.getElementById('sentMessageModal').style.display = 'none';
        }

        document.getElementById('msg_student_search').addEventListener('input', function () {
            const val = this.value;
            const options = document.querySelectorAll('#msgStudentList option');
            let matched = false;

            options.forEach(opt => {
                if (opt.value === val) {
                    document.getElementById('msg_student_id').value = opt.dataset.id;
                    document.getElementById('msg_student_email').value = opt.dataset.email;
                    matched = true;
                }
            });

            if (!matched) {
                document.getElementById('msg_student_id').value = '';
                document.getElementById('msg_student_email').value = '';
            }
        });
    </script>

</body>
</html>