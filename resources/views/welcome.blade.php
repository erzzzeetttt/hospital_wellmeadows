<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WellMeadows Hospital</title>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

<header class="navbar">
    <div class="logo">
        <span class="pulse">⌁</span>
        <span>WellMeadows Hospital</span>
    </div>

    <nav>
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}" class="register-btn">Register</a>
    </nav>
</header>

<section class="hero">
    <div class="hero-content">
        <h1>Healthcare Management<br>Excellence</h1>
        <p>
            Comprehensive hospital management system for patient care,
            staff coordination, and operational efficiency.
        </p>

        <div class="hero-buttons">
            <a href="{{ route('register') }}" class="btn primary">Get Started</a>
            <a href="#services" class="btn secondary">Learn More</a>
        </div>
    </div>
</section>

<section class="services" id="services">
    <h2>Our Services</h2>
    <p class="section-subtitle">
        Streamlined healthcare management solutions for modern hospitals
    </p>

    <div class="service-grid">
        <div class="service-card">
            <div class="icon">♡</div>
            <h3>Patient Management</h3>
            <p>Track admissions, discharges, and patient records with ease.</p>
        </div>

        <div class="service-card">
            <div class="icon">👥</div>
            <h3>Staff Coordination</h3>
            <p>Manage staff assignments, schedules, and qualifications.</p>
        </div>

        <div class="service-card">
            <div class="icon">▣</div>
            <h3>Appointment Scheduling</h3>
            <p>Efficient scheduling system for outpatients and consultations.</p>
        </div>

        <div class="service-card">
            <div class="icon">⬟</div>
            <h3>Secure Records</h3>
            <p>HIPAA-compliant data management and security protocols.</p>
        </div>

        <div class="service-card">
            <div class="icon">◷</div>
            <h3>Real-time Monitoring</h3>
            <p>Live updates on ward occupancy and patient status.</p>
        </div>

        <div class="service-card">
            <div class="icon">⌁</div>
            <h3>Analytics Dashboard</h3>
            <p>Comprehensive reporting and data visualization tools.</p>
        </div>
    </div>
</section>

</body>
</html>