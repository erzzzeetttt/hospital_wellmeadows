<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Profile | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module2css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/module2css/staff-profile.css') }}">
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
    <a href="{{ route('staff.index') }}" class="active">Staff Registration</a>
    <a href="{{ route('staff.ward-assignment') }}">Ward Assignment</a>
    <a href="{{ route('staff.schedule') }}">Staff Schedule</a>
</nav>

<main class="profile-container">

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <!-- Main Profile Card -->
    <div class="profile-card">

        <div class="profile-header">
            <div class="profile-name">
                <div class="profile-avatar">
                    {{ strtoupper(substr($staff->first_name, 0, 1)) }}{{ strtoupper(substr($staff->last_name, 0, 1)) }}
                </div>
                <div>
                    <h3>{{ $staff->first_name }} {{ $staff->last_name }}</h3>
                    <p>{{ $staff->position }} &bull; <span class="role-badge">{{ $staff->role_name ?? 'N/A' }}</span></p>
                </div>
            </div>
            <div class="profile-actions">
                <a href="{{ route('staff.edit', $staff->staff_no) }}" class="primary-btn">Edit Profile</a>
                <a href="{{ route('staff.index') }}" class="secondary-btn">Back to List</a>
            </div>
        </div>

        @if($staffProfile && $staffProfile->ward_name)
        <div class="current-assignment-card" style="padding:18px 20px; border-top:1px solid #e2e8f0; background:#f8fafc;">
            <h4 class="section-title" style="margin-bottom:12px;">Current Assignment</h4>
            <div class="info-grid">
                <div>
                    <label>Ward</label>
                    <strong>{{ $staffProfile->ward_name }}</strong>
                </div>
                <div>
                    <label>Role in Ward</label>
                    <strong>{{ $staffProfile->role_in_ward ?? 'Not set' }}</strong>
                </div>
                <div>
                    <label>Current Shift</label>
                    <strong>{{ $staffProfile->shift_type ?? 'Not set' }}</strong>
                </div>
                <div>
                    <label>Assigned Since</label>
                    <strong>{{ $staffProfile->assignment_date ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>
        @endif

        <div class="profile-body">

            <div class="section-title">Personal Information</div>

            <div class="info-grid">
                <div>
                    <label>Staff No</label>
                    <strong>S{{ str_pad($staff->staff_no, 3, '0', STR_PAD_LEFT) }}</strong>
                </div>
                <div>
                    <label>Full Name</label>
                    <strong>{{ $staff->first_name }} {{ $staff->last_name }}</strong>
                </div>
                <div>
                    <label>Date of Birth</label>
                    <strong>{{ $staff->dob }}</strong>
                </div>
                <div>
                    <label>Gender</label>
                    <strong>{{ $staff->gender }}</strong>
                </div>
                <div>
                    <label>Telephone Number</label>
                    <strong>{{ $staff->phone_no }}</strong>
                </div>
                <div>
                    <label>NIN</label>
                    <strong>{{ $staff->nin ?? '—' }}</strong>
                </div>
                <div>
                    <label>Date Registered</label>
                    <strong>{{ $staff->date_registered ?? '—' }}</strong>
                </div>
                <div>
                    <label>Address</label>
                    <strong>{{ $staff->address }}</strong>
                </div>
            </div>

            <div class="section-title">Position & Salary</div>

            <div class="info-grid">
                <div>
                    <label>Role</label>
                    <strong>{{ $staff->role_name ?? 'N/A' }}</strong>
                </div>
                <div>
                    <label>Position</label>
                    <strong>{{ $staff->position }}</strong>
                </div>
                <div>
                    <label>Current Salary</label>
                    <strong>{{ number_format($staff->salary, 2) }}</strong>
                </div>
                <div>
                    <label>Salary Scale</label>
                    <strong>{{ $staff->salary_scale ?? '—' }}</strong>
                </div>
            </div>

            <div class="section-title">Employment Contract</div>

            <div class="info-grid">
                <div>
                    <label>Hours / Week</label>
                    <strong>{{ $staff->hours_per_week ?? '—' }}</strong>
                </div>
                <div>
                    <label>Contract Type</label>
                    <strong>{{ $staff->contract_type ?? '—' }}</strong>
                </div>
                <div>
                    <label>Payment Type</label>
                    <strong>{{ $staff->payment_type ?? '—' }}</strong>
                </div>
            </div>

        </div>
    </div>

    <!-- Qualifications Card -->
    <div class="profile-card">
        <div class="panel-header">
            <div>
                <h3>Qualifications</h3>
                <p>Academic and professional credentials</p>
            </div>
        </div>
        <div class="profile-body">
            @forelse($qualifications as $qual)
                <div class="timeline-item">
                    <div>
                        <h5>{{ $qual->qualification_type }}</h5>
                        <p>{{ $qual->institution }}</p>
                    </div>
                    <span>{{ $qual->date_obtained }}</span>
                </div>
            @empty
                <div class="empty-note">No qualifications recorded for this staff member.</div>
            @endforelse
        </div>
    </div>

    <!-- Work Experience Card -->
    <div class="profile-card">
        <div class="panel-header">
            <div>
                <h3>Work Experience</h3>
                <p>Previous employment history</p>
            </div>
        </div>
        <div class="profile-body">
            @forelse($workExperiences as $exp)
                <div class="timeline-item">
                    <div>
                        <h5>{{ $exp->position }}</h5>
                        <p>{{ $exp->organization }}</p>
                    </div>
                    <span>{{ $exp->start_date }} — {{ $exp->end_date ?? 'Present' }}</span>
                </div>
            @empty
                <div class="empty-note">No work experience recorded for this staff member.</div>
            @endforelse
        </div>
    </div>

    <!-- Ward Assignments Card -->
    <div class="profile-card">
        <div class="panel-header">
            <div>
                <h3>Ward Assignments</h3>
                <p>Current and past ward assignments</p>
            </div>
        </div>
        <div class="profile-body">
            @forelse($wardAssignments as $assignment)
                <div class="timeline-item">
                    <div>
                        <h5>{{ $assignment->ward_name }}</h5>
                        <p>{{ $assignment->role_in_ward ?? 'General Assignment' }}</p>
                    </div>
                    <span>
                        {{ $assignment->assignment_date }}
                        @if($assignment->end_date)
                            — {{ $assignment->end_date }}
                        @else
                            &bull; <span class="status-badge active">Active</span>
                        @endif
                    </span>
                </div>
            @empty
                <div class="empty-note">No ward assignments for this staff member yet.</div>
            @endforelse
        </div>
    </div>

</main>

</body>
</html>
