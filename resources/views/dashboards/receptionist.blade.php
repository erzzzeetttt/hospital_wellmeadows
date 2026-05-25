<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard | WellMeadows</title>

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
            <a href="{{ route('patients.create') }}">Patient Registration</a>
            <a href="{{ route('patients.create') }}">Edit Patient Info</a>
            <a href="{{ route('module4.appointments') }}">Appointments</a>
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
                <p>Receptionist Dashboard</p>
            </div>
            <div class="profile">
                <div class="profile-text">
                    <span>{{ $name }}</span>
                    <small>Receptionist</small>
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
                    <span>Today's Appointments</span>
                    <h2>{{ $todayAppointments }}</h2>
                    <p>Active appointments today</p>
                </div>
                <div class="card">
                    <span>Total Patients</span>
                    <h2>{{ $totalPatients }}</h2>
                    <p>Registered patient records</p>
                </div>
            </section>

            <section>
                <h3 style="font-size:16px; font-weight:600; color:#0f172a; margin-bottom:16px;">Quick Actions</h3>
                <div class="shortcuts-grid">
                    <a href="{{ route('patients.create') }}" class="shortcut-card">
                        <div class="shortcut-label">Register Patient</div>
                        <div class="shortcut-desc">Add a new patient record</div>
                    </a>
                    <a href="{{ route('patients.create') }}" class="shortcut-card">
                        <div class="shortcut-label">Edit Patient Info</div>
                        <div class="shortcut-desc">Update existing patient details</div>
                    </a>
                    <a href="{{ route('module4.appointments') }}" class="shortcut-card">
                        <div class="shortcut-label">Appointments</div>
                        <div class="shortcut-desc">Schedule or view appointments</div>
                    </a>
                </div>
            </section>

        </main>

    </div>

</div>

</body>
</html>
