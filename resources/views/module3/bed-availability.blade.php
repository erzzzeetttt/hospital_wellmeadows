<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bed Availability | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module3css/wardbedmanagement.css') }}">
    {{-- Sub-nav anchor styles — replaces the radio-tab CSS selectors from wardbedmanagement.blade.php --}}
    <style>
        .sub-nav a { color:white; text-decoration:none; font-size:13px; padding:10px 14px; border-radius:5px; cursor:pointer; display:flex; align-items:center; height:100%; transition:background 0.2s; }
        .sub-nav a:hover { background:rgba(255,255,255,0.15); }
        .sub-nav a.active { background:rgba(255,255,255,0.2); font-weight:bold; }
    </style>
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Ward &amp; Bed Management System</p>
    </div>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</header>

<nav class="sub-nav" aria-label="Ward management sections">
    <a href="{{ route('ward-bed-management.index') }}">All Wards</a>
    <a href="{{ route('ward-bed-management.create') }}">Add Ward</a>
    <a href="{{ route('ward-bed-management.assign-bed') }}">Assign Bed</a>
    <a href="{{ route('ward-bed-management.bed-availability') }}" class="active">Bed Availability</a>
</nav>

@if(session('success'))
    <div class="ward-flash">
        <div class="alert-success">{{ session('success') }}</div>
    </div>
@endif

<main class="ward-main">
    <div class="ward-container">

        <div class="summary-grid">
            <div class="summary-card">
                <div>
                    <span>Total Beds</span>
                    <h3>{{ $stats['totalBeds'] }}</h3>
                    <p>All registered beds</p>
                </div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Vacant</span>
                    <h3>{{ $stats['vacantBeds'] }}</h3>
                    <p>Available for assignment</p>
                </div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Occupied</span>
                    <h3>{{ $stats['occupiedBeds'] }}</h3>
                    <p>Currently in use</p>
                </div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Maintenance</span>
                    <h3>{{ $stats['maintenanceBeds'] }}</h3>
                    <p>Out of service</p>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h3>Bed Availability by Ward</h3>
                    <p>View and filter bed status across all wards</p>
                </div>
                {{-- Ward filter posts to this page instead of the old single-page route --}}
                <form class="ward-filter" method="GET" action="{{ route('ward-bed-management.bed-availability') }}">
                    <select name="ward_id" onchange="this.form.submit()">
                        <option value="">All Wards</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->ward_id }}" @selected($selectedWardId === $ward->ward_id)>
                                {{ $ward->ward_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div style="padding: 18px;">
                @forelse($availabilityWards as $ward)
                    <div class="ward-block">
                        <div class="ward-block-header">
                            <div>
                                <strong>{{ $ward->ward_name }}</strong>
                                <span class="ward-type-label">{{ $ward->ward_type }} &middot; {{ $ward->location }}</span>
                            </div>
                            <div class="ward-counts">
                                <span class="count-vacant">{{ $ward->vacant_beds_count }} Vacant</span>
                                <span class="count-occupied">{{ $ward->occupied_beds_count }} Occupied</span>
                                <span class="count-maintenance">{{ $ward->maintenance_beds_count }} Maintenance</span>
                            </div>
                        </div>
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>Bed No.</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Patient</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ward->beds as $bed)
                                    <tr>
                                        <td>{{ $bed->bed_number }}</td>
                                        <td>Standard</td>
                                        <td>
                                            <span class="status-badge {{ $bed->status === 'Available' ? 'active' : ($bed->status === 'Occupied' ? 'occupied' : 'maintenance') }}">
                                                {{ $bed->status === 'Available' ? 'Vacant' : $bed->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($bed->activeAllocation?->patient)
                                                {{ $bed->activeAllocation->patient->first_name }} {{ $bed->activeAllocation->patient->last_name }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Release button for occupied beds --}}
                                            @if($bed->status === 'Occupied')
                                                <form method="POST" action="{{ route('ward-bed-management.release-bed', $bed->bed_id) }}"
                                                    style="display:inline;"
                                                    onsubmit="return confirm('Release this bed and end the current patient assignment?')">
                                                    @csrf
                                                    <button type="submit" style="background:#dc2626;color:white;border:none;padding:5px 10px;border-radius:4px;font-size:11px;cursor:pointer;">
                                                        Release
                                                    </button>
                                                </form>
                                            @endif
                                            {{-- Status update form --}}
                                            <form method="POST" action="{{ route('ward-bed-management.beds.status', $bed->bed_id) }}"
                                                style="display:inline-flex;gap:4px;align-items:center;margin-left:4px;">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" style="padding:4px 6px;border:1px solid #cbd5e1;border-radius:4px;font-size:11px;">
                                                    <option value="Available" {{ $bed->status === 'Available' ? 'selected' : '' }}>Available</option>
                                                    <option value="Occupied"  {{ $bed->status === 'Occupied'  ? 'selected' : '' }}>Occupied</option>
                                                    <option value="Maintenance" {{ $bed->status === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                </select>
                                                <button type="submit" style="background:#2563eb;color:white;border:none;padding:5px 10px;border-radius:4px;font-size:11px;cursor:pointer;">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="empty-note">No bed availability records to display yet.</div>
                @endforelse
            </div>
        </div>

    </div>
</main>

</body>
</html>
