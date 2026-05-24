<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Ward | WellMeadows</title>
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
    <a href="{{ route('ward-bed-management.create') }}" class="active">Add Ward</a>
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
                    <h3>Add New Ward</h3>
                    <p>Enter ward details to register a new ward in the system</p>
                </div>
            </div>

            <form method="POST" action="{{ route('ward-bed-management.wards.store') }}" style="padding: 26px;">
                @csrf

                <h4>Ward Information</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Ward Name *</label>
                        <input type="text" name="ward_name" value="{{ old('ward_name') }}" placeholder="e.g. Grampian" required>
                    </div>
                    <div class="form-group">
                        <label>Ward Type *</label>
                        <select name="ward_type" required>
                            <option value="">Select ward type</option>
                            <option @selected(old('ward_type') === 'Orthopaedic')>Orthopaedic</option>
                            <option @selected(old('ward_type') === 'Cardiology')>Cardiology</option>
                            <option @selected(old('ward_type') === 'General Surgery')>General Surgery</option>
                            <option @selected(old('ward_type') === 'Medical')>Medical</option>
                            <option @selected(old('ward_type') === 'Surgical')>Surgical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location *</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Block A, Floor 2" required>
                    </div>
                    <div class="form-group">
                        <label>Total Bed Capacity *</label>
                        <input type="number" name="total_beds" value="{{ old('total_beds') }}" min="1" placeholder="e.g. 12" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="reset" class="secondary-btn">Clear</button>
                    <button type="submit" class="primary-btn">Add Ward</button>
                </div>
            </form>
        </div>
    </div>
</main>

</body>
</html>
