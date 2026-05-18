<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Schedule | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/staff/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/staff-schedule.css') }}">
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
    <a href="{{ route('staff.ward-assignment') }}">Ward Assignment</a>
    <a href="{{ route('staff.schedule') }}" class="active">Staff Schedule</a>
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

    <!-- Summary Cards -->
    <section class="summary-grid">

        <div class="summary-card">
            <div>
                <span>Total Staff</span>
                <h3>{{ $totalStaff }}</h3>
                <p>All registered staff</p>
            </div>
            <div class="summary-icon">&#128101;</div>
        </div>

        <div class="summary-card">
            <div>
                <span>Assigned Staff</span>
                <h3>{{ $assignedStaff }}</h3>
                <p>Currently on duty</p>
            </div>
            <div class="summary-icon green">&#10003;</div>
        </div>

        <div class="summary-card">
            <div>
                <span>Unassigned Staff</span>
                <h3>{{ $totalStaff - $assignedStaff }}</h3>
                <p>Available for assignment</p>
            </div>
            <div class="summary-icon yellow">&#8987;</div>
        </div>

        <div class="summary-card">
            <div>
                <span>Total Wards</span>
                <h3>{{ $totalWards }}</h3>
                <p>Operational ward units</p>
            </div>
            <div class="summary-icon purple">&#127973;</div>
        </div>

    </section>

    <!-- Staff Schedule Report — Figure 1.2 style, grouped by ward -->
    <div class="panel-card">

        <div class="panel-header">
            <div>
                <h3>Staff Schedule Report</h3>
                <p>Active staff allocation by ward &mdash; Charge Nurse listed first per ward</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <button type="button" class="secondary-btn" onclick="window.print()">&#128438; Print Report</button>
                <button type="button" class="primary-btn" id="openRotaModal">+ Set Shift</button>
            </div>
        </div>

        @if($wardGroups->isEmpty())
            <div style="padding:30px;">
                <div class="empty-note">
                    No active ward assignments yet. Use
                    <a href="{{ route('staff.ward-assignment') }}" style="color:#2563eb;">Ward Assignment</a>
                    to assign staff to wards.
                </div>
            </div>
        @else

            @foreach($wardGroups as $wardId => $members)
                @php
                    $wardInfo    = $members->first();
                    $chargeNurse = $members->firstWhere('role_in_ward', 'Charge Nurse');
                @endphp

                <div class="ward-section">

                    <!-- Ward Header Bar -->
                    <div class="ward-section-header">
                        <div class="ward-title-block">
                            <h4>{{ $wardInfo->ward_name }}</h4>
                            @if($wardInfo->telephone_extension)
                                <span class="ward-tel">Tel Ext: {{ $wardInfo->telephone_extension }}</span>
                            @endif
                        </div>
                        <div class="charge-nurse-block">
                            @if($chargeNurse)
                                <span class="charge-nurse-label">
                                    &#9733; Charge Nurse:
                                    <strong>{{ $chargeNurse->first_name }} {{ $chargeNurse->last_name }}</strong>
                                </span>
                            @else
                                <span class="no-charge-nurse">&#9888; No Charge Nurse Assigned</span>
                            @endif
                        </div>
                    </div>

                    <!-- Staff Table for this Ward -->
                    <div class="table-wrapper" style="padding:0 18px 0;">
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Staff No</th>
                                    <th>Staff Name</th>
                                    <th>Position</th>
                                    <th>Role in Ward</th>
                                    <th>Current Shift</th>
                                    <th>Hours / Week</th>
                                    <th>Assigned Since</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                    @php
                                        $rota = $latestRota->get($member->staff_no . '_' . $member->ward_id);
                                    @endphp
                                    <tr class="{{ $member->role_in_ward === 'Charge Nurse' ? 'charge-nurse-row' : '' }}">
                                        <td>{{ $member->staff_no }}</td>
                                        <td>
                                            <a href="{{ route('staff.show', $member->staff_no) }}"
                                               style="color:#2563eb; text-decoration:none; font-weight:{{ $member->role_in_ward === 'Charge Nurse' ? '700' : '400' }};">
                                                {{ $member->first_name }} {{ $member->last_name }}
                                            </a>
                                        </td>
                                        <td>{{ $member->position }}</td>
                                        <td>
                                            @if($member->role_in_ward === 'Charge Nurse')
                                                <span class="charge-badge">Charge Nurse</span>
                                            @else
                                                {{ $member->role_in_ward ?: 'General' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($rota)
                                                @if($rota->shift_type === 'Early')
                                                    <span class="shift-badge shift-early">Early</span>
                                                @elseif($rota->shift_type === 'Late')
                                                    <span class="shift-badge shift-late">Late</span>
                                                @else
                                                    <span class="shift-badge shift-night">Night</span>
                                                @endif
                                            @else
                                                <span style="color:#94a3b8; font-size:11px;">Not set</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->hours_per_week ? $member->hours_per_week . ' hrs' : '—' }}</td>
                                        <td>{{ $member->assignment_date }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="ward-total-row">
                                    <td colspan="7">
                                        Total staff allocated to
                                        <strong>{{ $wardInfo->ward_name }}</strong>:
                                        <strong>{{ $members->count() }}</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            @endforeach

        @endif

    </div>

</main>

<!-- Set Shift Modal -->
<div id="rotaModal" class="modal-overlay">
    <div class="modal-box">

        <div class="modal-header">
            <h3>Set Weekly Shift</h3>
        </div>

        <form action="{{ route('staff.rota.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom:16px;">
                <label>Staff Member *</label>
                <select name="staff_no" required>
                    <option value="">Select staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->staff_no }}">
                            {{ $member->staff_no }} &mdash;
                            {{ $member->first_name }} {{ $member->last_name }}
                            ({{ $member->position }})
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
                <label>Week Starting (Monday) *</label>
                <input type="date" name="week_start_date" required>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Shift *</label>
                <select name="shift_type" required>
                    <option value="">Select shift</option>
                    <option value="Early">Early</option>
                    <option value="Late">Late</option>
                    <option value="Night">Night</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="secondary-btn" id="closeRotaModal">Cancel</button>
                <button type="submit" class="primary-btn">Save Shift</button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("rotaModal");

    document.getElementById("openRotaModal").addEventListener("click", function () {
        modal.style.display = "flex";
    });

    document.getElementById("closeRotaModal").addEventListener("click", function () {
        modal.style.display = "none";
    });

    modal.addEventListener("click", function (e) {
        if (e.target === modal) modal.style.display = "none";
    });
});
</script>

</body>
</html>
