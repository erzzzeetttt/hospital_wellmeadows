<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Wards | WellMeadows</title>
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
    <a href="{{ route('ward-bed-management.index') }}" class="active">All Wards</a>
    <a href="{{ route('ward-bed-management.create') }}">Add Ward</a>
    <a href="{{ route('ward-bed-management.assign-bed') }}">Assign Bed</a>
    <a href="{{ route('ward-bed-management.bed-availability') }}">Bed Availability</a>
</nav>

@if(session('success'))
    <div class="ward-flash">
        <div class="alert-success">{{ session('success') }}</div>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="ward-flash">
        <div class="alert-error">
            <strong>Please check the form and try again.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<main class="ward-main">
    <div class="ward-container">
        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h3>Available Wards</h3>
                    <p>Total of <strong>{{ $wards->count() }}</strong> wards registered in the system</p>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Ward ID</th>
                            <th>Ward Name</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Charge Nurse</th>
                            <th>Total Beds</th>
                            <th>Vacant</th>
                            <th>Occupied</th>
                            <th>Maintenance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wards as $ward)
                            <tr>
                                <td>W{{ str_pad((string) $ward->ward_id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td><strong>{{ $ward->ward_name }}</strong></td>
                                <td>{{ $ward->ward_type }}</td>
                                <td>{{ $ward->location }}</td>
                                <td>{{ $ward->charge_nurse }}</td>
                                <td>{{ $ward->beds_count }}</td>
                                <td><span class="count-vacant">{{ $ward->vacant_beds_count }}</span></td>
                                <td><span class="count-occupied">{{ $ward->occupied_beds_count }}</span></td>
                                <td><span class="count-maintenance">{{ $ward->maintenance_beds_count }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-cell">No wards have been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>
