<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Prefect of Discipline</title>

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
            <h2 class="page-title">Manage Students</h2>
            <input type="text" id="studentGlobalSearch" class="global-search" onkeyup="filterStudents()" placeholder="🔍 Search student name, ID, or email...">
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <!-- ADD NEW STUDENT FORM -->
        <div class="card">
            <h3>Add New Student</h3>
            <form action="/students" method="POST" autocomplete="off">
                @csrf
                <div class="form-grid-4">
                    <div class="form-group">
                        <label>Student Number</label>
                        <input type="text" name="student_number" class="form-control" placeholder="e.g. 2026-00123" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Juan Dela Cruz" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. juan@gmail.com" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="year_level" class="form-control" required>
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="course" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                @php $cCode = $course->course_code ?? $course->code ?? $course->name; @endphp
                                <option value="{{ $cCode }}">{{ $cCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Guardian Contact</label>
                        <input type="text" name="guardian_contact" class="form-control" placeholder="e.g. 09123456789" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary" style="width: 100%; height: 40px;">+ Add Student Record</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- COLLAPSIBLE COURSE DIRECTORIES (ASCENDING 1ST - 4TH YEAR) -->
        @php
            $yearOrder = ['1st Year' => 1, '2nd Year' => 2, '3rd Year' => 3, '4th Year' => 4];
        @endphp

        @forelse($courses as $course)
            @php
                $courseCode = $course->course_code ?? $course->code ?? $course->name;
                $courseStudents = $students
                    ->where('course', $courseCode)
                    ->sortBy(function ($s) use ($yearOrder) {
                        $rank = $yearOrder[$s->year_level] ?? 99;
                        return sprintf('%02d-%s', $rank, $s->student_number);
                    });
            @endphp

            <div class="card directory-card" id="dir-{{ Str::slug($courseCode) }}">
                <div class="directory-header" onclick="toggleDirectory('dir-{{ Str::slug($courseCode) }}')">
                    <h3 class="directory-title">
                        <span class="toggle-icon">▼</span>
                        {{ $courseCode }} Student Directory
                    </h3>
                    <span class="count-pill">{{ $courseStudents->count() }} Students</span>
                </div>

                <div class="directory-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student No.</th>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Yr Level</th>
                                <th>Guardian Contact</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courseStudents as $student)
                                <tr class="student-row">
                                    <td>#{{ $student->student_id }}</td>
                                    <td><span class="id-pill">{{ $student->student_number }}</span></td>
                                    <td><strong>{{ $student->name }}</strong></td>
                                    <td>
                                        @if(!empty($student->email))
                                            <span class="email-text">{{ $student->email }}</span>
                                        @else
                                            <span class="email-missing">No email set</span>
                                        @endif
                                    </td>
                                    <td><span class="yr-pill">{{ $student->year_level }}</span></td>
                                    <td>{{ $student->guardian_contact }}</td>
                                    <td>
                                        <div class="action-group">
                                            <button
                                                type="button"
                                                class="btn-edit"
                                                onclick="openEditModal(
                                                    '{{ $student->student_id }}',
                                                    '{{ addslashes($student->student_number) }}',
                                                    '{{ addslashes($student->name) }}',
                                                    '{{ addslashes($student->email ?? '') }}',
                                                    '{{ addslashes($student->year_level) }}',
                                                    '{{ addslashes($student->course) }}',
                                                    '{{ addslashes($student->guardian_contact) }}'
                                                )"
                                            >Edit</button>

                                            <form action="/students/{{ $student->student_id }}" method="POST" onsubmit="return confirm('Delete this student record?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        No students enrolled under {{ $courseCode }} yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card" style="text-align: center; color: #94a3b8;">
                No course categories configured.
            </div>
        @endforelse
    </main>

    <!-- EDIT STUDENT MODAL -->
    <div class="modal-backdrop" id="editStudentModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Student Record</h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editStudentForm" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="modal-grid">
                    <div class="form-group">
                        <label>Student Number</label>
                        <input type="text" name="student_number" id="edit_student_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Guardian Contact</label>
                        <input type="text" name="guardian_contact" id="edit_guardian_contact" class="form-control" required>
                    </div>
                    <div class="form-group full-span">
                        <label>Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group full-span">
                        <label>Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control" placeholder="e.g. juan@gmail.com">
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="year_level" id="edit_year_level" class="form-control" required>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="course" id="edit_course" class="form-control" required>
                            @foreach($courses as $course)
                                @php $cCode = $course->course_code ?? $course->code ?? $course->name; @endphp
                                <option value="{{ $cCode }}">{{ $cCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full-span">
                        <label>New Password <span style="font-weight:400; color:#94a3b8;">(Leave blank to keep current password)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-primary" style="width: auto;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleDirectory(cardId) {
            const card = document.getElementById(cardId);
            if (card) {
                card.classList.toggle('collapsed');
            }
        }

        function filterStudents() {
            const query = document.getElementById('studentGlobalSearch').value.toLowerCase();
            document.querySelectorAll('.directory-card').forEach(card => {
                const rows = card.querySelectorAll('.student-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const match = row.innerText.toLowerCase().includes(query);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                // Automatically expand matching directories while searching
                if (query.length > 0 && visibleCount > 0) {
                    card.classList.remove('collapsed');
                }
            });
        }

        function openEditModal(id, studentNumber, name, email, yearLevel, course, guardianContact) {
            document.getElementById('editStudentForm').action = '/students/' + id;
            document.getElementById('edit_student_number').value = studentNumber;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_year_level').value = yearLevel;
            document.getElementById('edit_course').value = course;
            document.getElementById('edit_guardian_contact').value = guardianContact;
            document.getElementById('editStudentModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('editStudentModal').classList.remove('open');
        }

        window.addEventListener('click', function (e) {
            const modal = document.getElementById('editStudentModal');
            if (e.target === modal) {
                closeEditModal();
            }
        });
    </script>

</body>
</html>