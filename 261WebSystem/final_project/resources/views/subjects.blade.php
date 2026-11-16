<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Prefect of Discipline</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        <div class="top-header">
            <h2 class="page-title">Manage Subjects</h2>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <!-- ADD NEW SUBJECT FORM -->
        <div class="card">
            <h3>Add New Subject</h3>
            <form action="/subjects" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Subject Code</label>
                        <input type="text" name="subject_code" class="form-control" placeholder="e.g. IT 101" required>
                    </div>
                    <div class="form-group">
                        <label>Subject Name</label>
                        <input type="text" name="subject_name" class="form-control" placeholder="e.g. Web Systems and Technologies" required>
                    </div>
                    <div class="form-group">
                        <label>Course Category</label>
                        <select name="course_id" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                @php $cCode = $course->course_code ?? $course->code ?? $course->name; @endphp
                                <option value="{{ $course->course_id }}">{{ $cCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Schedule Days</label>
                        <input type="text" name="schedule_days" class="form-control" placeholder="e.g. Mon & Thu" required>
                    </div>
                    <div class="form-group">
                        <label>Schedule Time</label>
                        <input type="text" name="schedule_time" class="form-control" placeholder="e.g. 5:00 PM - 6:00 PM" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Add Subject Record</button>
            </form>
        </div>

        <!-- SUBJECT DIRECTORY -->
        <div class="card">
            <h3>Subject Directory</h3>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Subject Name</th>
                            <th>Course</th>
                            <th>Schedule</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $sub)
                            <tr>
                                <td>#{{ $sub->subject_id }}</td>
                                <td><strong>{{ $sub->subject_code }}</strong></td>
                                <td>{{ $sub->subject_name }}</td>
                                <td>{{ $sub->course_code ?? 'N/A' }}</td>
                                <td>
                                    <div>
                                        <span class="badge-day">{{ $sub->schedule_days }}</span>
                                        <span class="badge-time">{{ $sub->schedule_time }}</span>
                                    </div>
                                    @if(!empty($sub->schedule_days_2))
                                        <div style="margin-top: 4px;">
                                            <span class="badge-day">{{ $sub->schedule_days_2 }}</span>
                                            <span class="badge-time">{{ $sub->schedule_time_2 }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            onclick="openEditSubjectModal(
                                                '{{ $sub->subject_id }}',
                                                '{{ addslashes($sub->subject_code) }}',
                                                '{{ addslashes($sub->subject_name) }}',
                                                '{{ $sub->course_id }}',
                                                '{{ addslashes($sub->schedule_days ?? '') }}',
                                                '{{ addslashes($sub->schedule_time ?? '') }}',
                                                '{{ addslashes($sub->schedule_days_2 ?? '') }}',
                                                '{{ addslashes($sub->schedule_time_2 ?? '') }}'
                                            )"
                                        >Edit</button>

                                        <form method="POST" action="/subjects/{{ $sub->subject_id }}" onsubmit="return confirm('Delete subject?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">
                                    No subjects registered yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- EDIT SUBJECT MODAL -->
    <div class="modal-backdrop" id="editSubjectModal">
        <div class="modal-box" style="max-width: 540px;">
            <div class="modal-header">
                <h3>Edit Subject Record</h3>
                <button type="button" class="modal-close" onclick="closeEditSubjectModal()">&times;</button>
            </div>
            <form id="editSubjectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-grid">
                    <div class="form-group">
                        <label>Subject Code</label>
                        <input type="text" name="subject_code" id="edit_subject_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Course Category</label>
                        <select name="course_id" id="edit_course_id" class="form-control" required>
                            @foreach($courses as $course)
                                @php $cCode = $course->course_code ?? $course->code ?? $course->name; @endphp
                                <option value="{{ $course->course_id }}">{{ $cCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full-span">
                        <label>Subject Name</label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Schedule Days (Primary)</label>
                        <input type="text" name="schedule_days" id="edit_schedule_days" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Schedule Time (Primary)</label>
                        <input type="text" name="schedule_time" id="edit_schedule_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Schedule Days 2 <span style="font-weight:400; color:#94a3b8;">(Optional)</span></label>
                        <input type="text" name="schedule_days_2" id="edit_schedule_days_2" class="form-control" placeholder="e.g. Fri">
                    </div>
                    <div class="form-group">
                        <label>Schedule Time 2 <span style="font-weight:400; color:#94a3b8;">(Optional)</span></label>
                        <input type="text" name="schedule_time_2" id="edit_schedule_time_2" class="form-control" placeholder="e.g. 1:00 PM - 2:00 PM">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditSubjectModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditSubjectModal(id, code, name, courseId, days1, time1, days2, time2) {
            document.getElementById('editSubjectForm').action = '/subjects/' + id;
            document.getElementById('edit_subject_code').value = code;
            document.getElementById('edit_subject_name').value = name;
            document.getElementById('edit_course_id').value = courseId;
            document.getElementById('edit_schedule_days').value = days1;
            document.getElementById('edit_schedule_time').value = time1;
            document.getElementById('edit_schedule_days_2').value = days2;
            document.getElementById('edit_schedule_time_2').value = time2;
            document.getElementById('editSubjectModal').classList.add('open');
        }

        function closeEditSubjectModal() {
            document.getElementById('editSubjectModal').classList.remove('open');
        }

        window.addEventListener('click', function (e) {
            const modal = document.getElementById('editSubjectModal');
            if (e.target === modal) {
                closeEditSubjectModal();
            }
        });
    </script>

</body>
</html>