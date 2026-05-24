<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Bed | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module3css/wardassignment.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Ward &amp; Bed Management System</p>
    </div>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('ward-bed-management.index') }}">All Wards</a>
    <a href="{{ route('ward-bed-management.create') }}">Add Ward</a>
    <a href="{{ route('ward-bed-management.assign-bed') }}" class="active">Assign Bed</a>
    <a href="{{ route('ward-bed-management.bed-availability') }}">Bed Availability</a>
</nav>

<main class="ward-container">

    @if(session('success'))
        <div style="background:#dcfce7;color:#166534;padding:12px 20px;border-radius:8px;margin-bottom:18px;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2;color:#991b1b;padding:12px 20px;border-radius:8px;margin-bottom:18px;font-size:13px;">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px 20px;border-radius:8px;margin-bottom:18px;font-size:13px;">
            <strong>Please fix these errors:</strong>
            <ul style="margin:6px 0 0 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ward summary cards --}}
    <section class="ward-summary-grid">
        @forelse($wardStats as $ward)
            <div class="ward-card">
                <h3>{{ $ward->ward_name }}</h3>
                <div class="ward-stat"><span>Total Beds</span><strong>{{ $ward->beds_count }}</strong></div>
                <div class="ward-stat"><span>Occupied</span><strong class="danger">{{ $ward->occupied_beds_count }}</strong></div>
                <div class="ward-stat"><span>Available</span><strong class="success">{{ $ward->vacant_beds_count }}</strong></div>
                @if($ward->beds_count > 0)
                    <div class="progress">
                        <div style="width:{{ round(($ward->occupied_beds_count / $ward->beds_count) * 100) }}%"></div>
                    </div>
                @endif
            </div>
        @empty
            <div style="grid-column:1/-1;padding:16px;color:#64748b;font-size:13px;">No wards registered yet.</div>
        @endforelse
    </section>

    <section class="ward-main-grid">

        {{-- Left: Assignment Form --}}
        <div class="bed-layout-card">
            <div class="card-title">
                <div>
                    <h3>Assign Patient to Bed</h3>
                    <p>Select an admitted patient, ward, and available bed</p>
                </div>
            </div>

            <form method="POST" action="{{ route('ward-bed-management.assign-bed.store') }}">
                @csrf

                <div class="form-group">
                    <label>Select Patient *</label>
                    <select name="patient_no" required>
                        <option value="">Choose admitted patient...</option>
                        @foreach($admittedPatients as $admission)
                            <option value="{{ $admission->patient_no }}"
                                {{ old('patient_no') === $admission->patient_no ? 'selected' : '' }}>
                                {{ $admission->patient_no }} — {{ $admission->patient?->first_name }} {{ $admission->patient?->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Ward *</label>
                    <select name="ward_id" id="wardSelect" required>
                        <option value="">Select ward...</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->ward_id }}"
                                {{ (string) old('ward_id') === (string) $ward->ward_id ? 'selected' : '' }}>
                                {{ $ward->ward_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Available Bed *</label>
                    {{-- Filtered by ward via JS using data-ward attribute --}}
                    <select name="bed_id" id="bedSelect" required>
                        <option value="">Select bed...</option>
                        @foreach($availableBeds as $bed)
                            <option value="{{ $bed->bed_id }}"
                                data-ward="{{ $bed->ward_id }}"
                                {{ (string) old('bed_id') === (string) $bed->bed_id ? 'selected' : '' }}>
                                {{ $bed->ward->ward_name }} — Bed {{ $bed->bed_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Allocation Date *</label>
                    <input type="date" name="allocation_date"
                        value="{{ old('allocation_date', now()->format('Y-m-d')) }}" required>
                </div>

                <button type="submit" class="assign-btn">Assign to Bed</button>
            </form>
        </div>

        {{-- Right: Recent Assignments --}}
        <div class="assign-card">
            <h3>Recent Assignments</h3>

            <div class="recent-section" style="margin-top:16px;border-top:none;padding-top:0;">
                @forelse($recentAssignments as $assignment)
                    <div class="recent-item">
                        <strong>
                            {{ $assignment->patient?->first_name }} {{ $assignment->patient?->last_name }}
                            — {{ $assignment->ward?->ward_name }} Bed {{ $assignment->bed?->bed_number }}
                        </strong>
                        <span>{{ $assignment->allocation_date }}</span>
                        @if($assignment->release_date)
                            <span style="color:#16a34a;font-size:11px;display:block;">Released {{ $assignment->release_date }}</span>
                        @else
                            <span style="color:#dc2626;font-size:11px;display:block;">Active</span>
                        @endif
                    </div>
                @empty
                    <p style="font-size:13px;color:#64748b;">No assignments recorded yet.</p>
                @endforelse
            </div>
        </div>

    </section>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wardSelect = document.getElementById('wardSelect');
        const bedSelect  = document.getElementById('bedSelect');

        function filterBeds() {
            const selectedWard = wardSelect.value;
            Array.from(bedSelect.options).forEach(function (opt) {
                if (!opt.value) { opt.hidden = false; return; }
                opt.hidden = opt.dataset.ward !== selectedWard;
            });
            if (bedSelect.selectedOptions[0]?.hidden) bedSelect.value = '';
        }

        wardSelect.addEventListener('change', filterBeds);
        filterBeds();
    });
</script>

</body>
</html>
