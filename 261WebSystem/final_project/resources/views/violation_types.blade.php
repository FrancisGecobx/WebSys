<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Violation Types - Prefect of Discipline</title>

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
            <h2 class="page-title">Violation Types</h2>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <!-- ADD VIOLATION TYPE FORM -->
        <div class="card">
            <h3>Add Violation Type</h3>
            <form action="/violation-types" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Violation Name</label>
                        <input type="text" name="violation_name" class="form-control" placeholder="e.g. Uniform Offense / Vandalism" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Severity Level</label>
                        <select name="severity_level" class="form-control" required>
                            <option value="Minor">Minor</option>
                            <option value="Major">Major</option>
                            <option value="Severe">Severe</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description (optional)</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Provide guidelines or criteria regarding this violation type..."></textarea>
                </div>
                <button type="submit" class="btn-primary">Add Violation Category</button>
            </form>
        </div>

        <!-- CATEGORY DIRECTORY -->
        <div class="card">
            <h3>Category Directory</h3>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Violation Name</th>
                            <th>Description</th>
                            <th>Severity Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $items = $violationTypes ?? $violations ?? $types ?? \Illuminate\Support\Facades\DB::table('violation_types')->orderByDesc('violation_type_id')->get();
                        @endphp
                        @forelse($items as $vt)
                            @php
                                $sev = strtolower($vt->severity_level ?? 'minor');
                                $sevClass = $sev === 'severe' ? 'sev-severe' : ($sev === 'major' ? 'sev-major' : 'sev-minor');
                            @endphp
                            <tr>
                                <td>#{{ $vt->violation_type_id }}</td>
                                <td><strong>{{ $vt->violation_name }}</strong></td>
                                <td>{{ $vt->description ?? '—' }}</td>
                                <td><span class="sev-badge {{ $sevClass }}">{{ $vt->severity_level }}</span></td>
                                <td>
                                    <div class="action-group">
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            onclick="openEditViolationModal(
                                                '{{ $vt->violation_type_id }}',
                                                '{{ addslashes($vt->violation_name) }}',
                                                '{{ addslashes($vt->severity_level) }}',
                                                '{{ addslashes($vt->description ?? '') }}'
                                            )"
                                        >Edit</button>

                                        <form method="POST" action="/violation-types/{{ $vt->violation_type_id }}" onsubmit="return confirm('Delete this violation category?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px;">
                                    No violation types configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- EDIT VIOLATION MODAL -->
    <div class="modal-backdrop" id="editViolationModal">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Edit Violation Category</h3>
                <button type="button" class="modal-close" onclick="closeEditViolationModal()">&times;</button>
            </div>
            <form id="editViolationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Violation Name</label>
                    <input type="text" name="violation_name" id="edit_violation_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Severity Level</label>
                    <select name="severity_level" id="edit_severity_level" class="form-control" required>
                        <option value="Minor">Minor</option>
                        <option value="Major">Major</option>
                        <option value="Severe">Severe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description (optional)</label>
                    <textarea name="description" id="edit_violation_description" rows="3" class="form-control"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditViolationModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditViolationModal(id, name, severity, description) {
            document.getElementById('editViolationForm').action = '/violation-types/' + id;
            document.getElementById('edit_violation_name').value = name;
            document.getElementById('edit_severity_level').value = severity;
            document.getElementById('edit_violation_description').value = description;
            document.getElementById('editViolationModal').classList.add('open');
        }

        function closeEditViolationModal() {
            document.getElementById('editViolationModal').classList.remove('open');
        }

        window.addEventListener('click', function (e) {
            const modal = document.getElementById('editViolationModal');
            if (e.target === modal) {
                closeEditViolationModal();
            }
        });
    </script>

</body>
</html>