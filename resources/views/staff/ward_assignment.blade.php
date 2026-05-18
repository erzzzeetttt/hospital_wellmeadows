<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ward Assignment | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/staff/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/ward-assignment.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Staff Management System</p>
    </div>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('staff.index') }}">Staff Registration</a>
    <a href="{{ route('staff.ward-assignment') }}" class="active">Ward Assignment</a>
    <a href="{{ route('staff.schedule') }}">Staff Schedule</a>
</nav>

<main class="staff-container">

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            <strong>Please fix these errors:</strong>
            <ul style="margin-left:20px; margin-top:8px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tracking-grid">

        <!-- Active Assignments Panel (left) -->
        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h3>Active Ward Assignments</h3>
                    <p>Staff currently assigned to wards</p>
                </div>
                <button type="button" class="primary-btn" id="openAssignModal">
                    + New Assignment
                </button>
            </div>

            <div class="panel-body">

                @forelse($assignments as $assignment)
                    <div class="staff-item">
                        <div class="staff-item-top">
                            <div>
                                <h4>{{ $assignment->first_name }} {{ $assignment->last_name }}</h4>
                                <p>{{ $assignment->position }} &bull; Assigned: {{ $assignment->assignment_date }}</p>
                            </div>
                            <div style="display:flex; gap:6px; align-items:center;">
                                @if($assignment->role_in_ward === 'Charge Nurse')
                                    <span class="status-badge" style="background:#fef3c7; color:#92400e;">&#9733; Charge Nurse</span>
                                @else
                                    <span class="status-badge active">Active</span>
                                @endif
                            </div>
                        </div>

                        <div class="staff-info">
                            <div>
                                <label>Ward</label>
                                <strong>{{ $assignment->ward_name }}</strong>
                            </div>
                            <div>
                                <label>Role in Ward</label>
                                <strong>{{ $assignment->role_in_ward ?? 'General' }}</strong>
                            </div>
                            <div>
                                <label>Assignment ID</label>
                                <strong>#{{ $assignment->assignment_id }}</strong>
                            </div>
                        </div>

                        <div class="staff-actions">
                            <a href="{{ route('staff.show', $assignment->staff_no) }}" class="secondary-btn">
                                View Profile
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-note">No active ward assignments yet.</div>
                @endforelse

            </div>
        </div>

        <!-- Quick Assign Form (right) -->
        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h3>Quick Assign</h3>
                    <p>Assign a staff member to a ward</p>
                </div>
            </div>

            <div class="panel-body">
                <form action="{{ route('staff.ward-assignment.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Staff Member *</label>
                        <select name="staff_no" required>
                            <option value="">Select staff member</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->staff_no }}">
                                    {{ $member->first_name }} {{ $member->last_name }} — {{ $member->position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ward *</label>
                        <select name="ward_id" required>
                            <option value="">Select ward</option>
                            @foreach($wards as $ward)
                                <option value="{{ $ward->ward_id }}">{{ $ward->ward_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assignment Date *</label>
                        <input type="date" name="assignment_date" required>
                    </div>

                    <div class="form-group">
                        <label>Role in Ward</label>
                        <select name="role_in_ward">
                            <option value="">General Assignment</option>
                            <option value="Charge Nurse">Charge Nurse</option>
                            <option value="Staff Nurse">Staff Nurse</option>
                            <option value="Nurse">Nurse</option>
                            <option value="Doctor">Doctor</option>
                            <option value="Consultant">Consultant</option>
                            <option value="Auxiliary">Auxiliary</option>
                            <option value="Physiotherapist">Physiotherapist</option>
                        </select>
                    </div>

                    <button type="submit" class="primary-btn assign-full-btn">
                        Assign to Ward
                    </button>

                </form>
            </div>
        </div>

    </div>

</main>

<!-- New Assignment Modal -->
<div id="assignModal" class="modal-overlay">
    <div class="modal-box">

        <div class="modal-header">
            <h3>New Ward Assignment</h3>
        </div>

        <form action="{{ route('staff.ward-assignment.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom:16px;">
                <label>Staff Member *</label>
                <select name="staff_no" required>
                    <option value="">Select staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->staff_no }}">
                            {{ $member->first_name }} {{ $member->last_name }} — {{ $member->position }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Ward *</label>
                <select name="ward_id" required>
                    <option value="">Select ward</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->ward_id }}">{{ $ward->ward_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Assignment Date *</label>
                <input type="date" name="assignment_date" required>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Role in Ward</label>
                <select name="role_in_ward">
                    <option value="">General Assignment</option>
                    <option value="Charge Nurse">Charge Nurse</option>
                    <option value="Staff Nurse">Staff Nurse</option>
                    <option value="Nurse">Nurse</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Consultant">Consultant</option>
                    <option value="Auxiliary">Auxiliary</option>
                    <option value="Physiotherapist">Physiotherapist</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="secondary-btn" id="closeAssignModal">Cancel</button>
                <button type="submit" class="primary-btn">Assign Staff</button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("assignModal");

    document.getElementById("openAssignModal").addEventListener("click", function () {
        modal.style.display = "flex";
    });

    document.getElementById("closeAssignModal").addEventListener("click", function () {
        modal.style.display = "none";
    });
});
</script>

</body>
</html>
