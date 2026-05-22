<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | WellMeadows</title>

    <link rel="stylesheet" href="{{ asset('css/admindash.css') }}">
</head>
<body>

@php
    $name = Auth::user()->name ?? 'Admin User';
    $words = explode(' ', $name);

    $initials = strtoupper(
        substr($words[0], 0, 1) .
        (isset($words[1]) ? substr($words[1], 0, 1) : '')
    );
@endphp

<div class="dashboard">

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">⌁</div>
            <h2>WellMeadows</h2>
        </div>

        <nav class="nav-tabs">
            <a href="#" class="active">Dashboard</a>
            <a href="{{ route('patients.create') }}">Patient Management</a>
            <a href="{{ route('staff.index') }}">Staff Management</a>
            <a href="{{ route('ward-bed-management.index') }}">Ward and Bed Management</a>
            <a href="#">Appointments and Treatments</a>
            <a href="#">Billings and Reports</a>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="logout-btn">
            @csrf
            <button type="submit">Logout</button>
        </form>

    </aside>

    <div class="main-area">

        <header class="topbar">

            <div>
                <h1>Welcome, {{ Auth::user()->name }}</h1>
                <p>Administrator Dashboard</p>
            </div>

            <div class="profile">

                <div class="profile-text">
                    <span>{{ $name }}</span>
                    <small>Administrator</small>
                </div>

                <div class="avatar">
                    {{ $initials }}
                </div>

            </div>

        </header>

        <main class="content">

            <section class="cards">

                <div class="card">
                    <span>Total Users</span>
                    <h2>{{ $totalUsers }}</h2>
                    <p>Registered system users</p>
                </div>

                <div class="card">
                    <span>Total Patients</span>
                    <h2>{{ $totalPatients }}</h2>
                    <p>Hospital patient records</p>
                </div>

                <div class="card">
                    <span>Total Wards</span>
                    <h2>0</h2>
                    <p>All operational wards</p>
                </div>

                <div class="card">
                    <span>Available Beds</span>
                    <h2>0</h2>
                    <p>Ready for allocation</p>
                </div>

            </section>

        </main>

    </div>

</div>

</body>
</html>
