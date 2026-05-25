<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge Nurse Dashboard | WellMeadows</title>

    <link rel="stylesheet" href="{{ asset('css/admindash.css') }}">
</head>
<body>

@php
    $words    = explode(' ', $name);
    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
@endphp

<div class="dashboard">

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">⌁</div>
            <h2>WellMeadows</h2>
        </div>

        <nav class="nav-tabs">
            <a href="#" class="active">Dashboard</a>
            <a href="{{ route('medical-records.index') }}">Medical Records</a>
            <a href="{{ route('ward-bed-management.index') }}">Ward Management</a>
            <a href="{{ route('admission-tracking.index') }}">Admissions</a>
            <a href="{{ route('medical-records.index') }}">Medication Monitoring</a>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="logout-btn">
            @csrf
            <button type="submit">Logout</button>
        </form>

    </aside>

    <div class="main-area">

        <header class="topbar">
            <div>
                <h1>Welcome, {{ $name }}</h1>
                <p>Charge Nurse Dashboard</p>
            </div>
            <div class="profile">
                <div class="profile-text">
                    <span>{{ $name }}</span>
                    <small>Charge Nurse</small>
                </div>
                <div class="avatar">{{ $initials }}</div>
            </div>
        </header>

        <main class="content">

            @if (session('error'))
                <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:24px; font-size:14px;">
                    {{ session('error') }}
                </div>
            @endif

            <section class="cards" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 32px;">
                <div class="card">
                    <span>Occupied Beds</span>
                    <h2>{{ $occupiedBeds }}</h2>
                    <p>Currently occupied beds</p>
                </div>
                <div class="card">
                    <span>Admitted Patients</span>
                    <h2>{{ $admittedPatients }}</h2>
                    <p>Currently admitted patients</p>
                </div>
            </section>

            <section>
                <h3 style="font-size:16px; font-weight:600; color:#0f172a; margin-bottom:16px;">Quick Actions</h3>
                <div class="shortcuts-grid">
                    <a href="{{ route('medical-records.index') }}" class="shortcut-card">
                        <div class="shortcut-label">Medical Records</div>
                        <div class="shortcut-desc">View and manage patient records</div>
                    </a>
                    <a href="{{ route('ward-bed-management.index') }}" class="shortcut-card">
                        <div class="shortcut-label">Ward Management</div>
                        <div class="shortcut-desc">Wards, beds and availability</div>
                    </a>
                    <a href="{{ route('admission-tracking.index') }}" class="shortcut-card">
                        <div class="shortcut-label">Admissions</div>
                        <div class="shortcut-desc">Admit and discharge patients</div>
                    </a>
                    <a href="{{ route('medical-records.index') }}" class="shortcut-card">
                        <div class="shortcut-label">Medication</div>
                        <div class="shortcut-desc">Monitor medication records</div>
                    </a>
                </div>
            </section>

        </main>

    </div>

</div>

</body>
</html>
