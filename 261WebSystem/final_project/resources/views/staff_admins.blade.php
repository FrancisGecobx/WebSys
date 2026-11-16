<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Prefect of Discipline</title>

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
            <h2 class="page-title">Manage Staff & Admins</h2>
            <input type="text" id="adminSearchInput" class="global-search" onkeyup="filterAdmins()" placeholder="🔍 Search name, email, or role...">
        </div>

        @if(session('success'))
            <div class="alert-success">✓ {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">⚠️ {{ $errors->first() }}</div>
        @endif

        <!-- ADD NEW ADMIN FORM -->
        <div class="card">
            <h3>Add New Staff / Admin Account</h3>
            <form action="/staff-admins" method="POST" autocomplete="off">
                @csrf
                <div class="form-grid-4">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Prof. Maria Santos" required>
                    </div>
                    <div class="form-group">
                        <label>Institutional Email</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. pod.staff@aldersgate.edu.ph" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="Prefect of Discipline">Prefect of Discipline</option>
                            <option value="Assistant POD">Assistant POD</option>
                            <option value="Guidance Counselor">Guidance Counselor</option>
                            <option value="OSA Staff">OSA Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Department / Office</label>
                        <input type="text" name="department" class="form-control" value="Office of Student Affairs" required>
                    </div>
                    <div class="form-group">
                        <label>Account Status</label>
                        <select name="is_active" class="form-control" required>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label>Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary" style="width: 100%; height: 40px;">+ Create Admin Account</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- STAFF ADMINS DIRECTORY TABLE -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0;">Authorized POD Staff & Admin Accounts</h3>
                <span style="background: #dcfce7; color: #166534; font-size: 0.775rem; font-weight: 700; padding: 5px 12px; border-radius: 20px;">
                    {{ $admins->count() }} {{ $admins->count() === 1 ? 'Account' : 'Accounts' }}
                </span>
            </div>

            <div style="width: 100%; overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr class="admin-row">
                                <td>#{{ $admin->staff_admin_id }}</td>
                                <td>
                                    <strong>{{ $admin->name }}</strong>
                                    @if((int) $admin->staff_admin_id === (int) session('staff_admin_id'))
                                        <span class="you-badge">YOU</span>
                                    @endif
                                </td>
                                <td>{{ $admin->email }}</td>
                                <td><span class="role-pill">{{ $admin->role }}</span></td>
                                <td>{{ $admin->department }}</td>
                                <td>
                                    @if($admin->is_active)
                                        <span class="status-active">ACTIVE</span>
                                    @else
                                        <span class="status-inactive">INACTIVE</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($admin->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-group">
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            onclick="openEditModal(
                                                '{{ $admin->staff_admin_id }}',
                                                '{{ addslashes($admin->name) }}',
                                                '{{ addslashes($admin->email) }}',
                                                '{{ addslashes($admin->role) }}',
                                                '{{ addslashes($admin->department) }}',
                                                '{{ $admin->is_active }}'
                                            )"
                                        >Edit</button>

                                        @if((int) $admin->staff_admin_id !== (int) session('staff_admin_id'))
                                            <form action="/staff-admins/{{ $admin->staff_admin_id }}" method="POST" onsubmit="return confirm('Delete this admin account?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">
                                    No staff admin records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- EDIT ADMIN MODAL -->
    <div class="modal-backdrop" id="editAdminModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Staff / Admin Account</h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editAdminForm" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="modal-grid">
                    <div class="form-group full-span">
                        <label>Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group full-span">
                        <label>Institutional Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="Prefect of Discipline">Prefect of Discipline</option>
                            <option value="Assistant POD">Assistant POD</option>
                            <option value="Guidance Counselor">Guidance Counselor</option>
                            <option value="OSA Staff">OSA Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" id="edit_is_active" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group full-span">
                        <label>Department / Office</label>
                        <input type="text" name="department" id="edit_department" class="form-control" required>
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
        function filterAdmins() {
            const query = document.getElementById('adminSearchInput').value.toLowerCase();
            document.querySelectorAll('.admin-row').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        }

        function openEditModal(id, name, email, role, department, isActive) {
            document.getElementById('editAdminForm').action = '/staff-admins/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_department').value = department;
            document.getElementById('edit_is_active').value = isActive;
            document.getElementById('editAdminModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('editAdminModal').classList.remove('open');
        }

        window.addEventListener('click', function (e) {
            const modal = document.getElementById('editAdminModal');
            if (e.target === modal) {
                closeEditModal();
            }
        });
    </script>

</body>
</html>