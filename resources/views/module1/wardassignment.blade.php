<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ward Assignment | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module1css/wardassignment.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Patient Management System</p>
    </div>

    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('patients.create') }}">Patient Registration</a>
    <a href="{{ route('medical-records.index') }}">Medical Records</a>

    <a href="{{ route('admission-tracking.index') }}">Admission Tracking</a>
</nav>

<main class="ward-container">

    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px 20px;margin-bottom:22px;font-size:13px;color:#1e40af;">
        <strong>Notice:</strong> Ward Assignment has been moved to
        <strong>Module 3 &mdash; Ward &amp; Bed Management</strong>.
        <a href="{{ route('ward-bed-management.assign-bed') }}"
           style="color:#2563eb;font-weight:bold;text-decoration:underline;margin-left:6px;">
            Go to Assign Bed &rarr;
        </a>
    </div>

    <section class="ward-summary-grid">

        <div class="ward-card active">
            <h3>Cardiology</h3>
            <div class="ward-stat"><span>Total Beds</span><strong>20</strong></div>
            <div class="ward-stat"><span>Occupied</span><strong class="danger">15</strong></div>
            <div class="ward-stat"><span>Available</span><strong class="success">5</strong></div>
            <div class="progress"><div style="width:75%"></div></div>
        </div>

        <div class="ward-card">
            <h3>Pediatrics</h3>
            <div class="ward-stat"><span>Total Beds</span><strong>15</strong></div>
            <div class="ward-stat"><span>Occupied</span><strong class="danger">12</strong></div>
            <div class="ward-stat"><span>Available</span><strong class="success">3</strong></div>
            <div class="progress"><div style="width:80%"></div></div>
        </div>

        <div class="ward-card">
            <h3>Orthopedics</h3>
            <div class="ward-stat"><span>Total Beds</span><strong>18</strong></div>
            <div class="ward-stat"><span>Occupied</span><strong class="danger">10</strong></div>
            <div class="ward-stat"><span>Available</span><strong class="success">8</strong></div>
            <div class="progress"><div style="width:55%"></div></div>
        </div>

        <div class="ward-card">
            <h3>Emergency</h3>
            <div class="ward-stat"><span>Total Beds</span><strong>25</strong></div>
            <div class="ward-stat"><span>Occupied</span><strong class="danger">20</strong></div>
            <div class="ward-stat"><span>Available</span><strong class="success">5</strong></div>
            <div class="progress"><div style="width:80%"></div></div>
        </div>

    </section>

    <section class="ward-main-grid">

        <div class="bed-layout-card">
            <div class="card-title">
                <div>
                    <h3>Cardiology Ward</h3>
                    <p>Bed Layout & Status</p>
                </div>

                <div class="legend">
                    <span><i class="available-dot"></i> Available</span>
                    <span><i class="occupied-dot"></i> Occupied</span>
                    <span><i class="maintenance-dot"></i> Maintenance</span>
                </div>
            </div>

            <div class="bed-grid">

                <div class="bed-card occupied">
                    <strong>C-101</strong>
                    <p>John Smith</p>
                    <small>Since 2026-04-15</small>
                </div>

                <div class="bed-card occupied">
                    <strong>C-102</strong>
                    <p>Mary Davis</p>
                    <small>Since 2026-05-01</small>
                </div>

                <div class="bed-card available">
                    <strong>C-103</strong>
                    <p>Available</p>
                </div>

                <div class="bed-card occupied">
                    <strong>C-104</strong>
                    <p>Robert Wilson</p>
                    <small>Since 2026-04-28</small>
                </div>

                <div class="bed-card maintenance">
                    <strong>C-105</strong>
                    <p>Maintenance</p>
                </div>

                <div class="bed-card available">
                    <strong>C-106</strong>
                    <p>Available</p>
                </div>

                <div class="bed-card occupied">
                    <strong>C-107</strong>
                    <p>Linda Martinez</p>
                    <small>Since 2026-05-03</small>
                </div>

                <div class="bed-card available">
                    <strong>C-108</strong>
                    <p>Available</p>
                </div>

            </div>
        </div>

        <div class="assign-card">
            <h3>Assign Patient to Bed</h3>

            <form>
                <div class="form-group">
                    <label>Select Patient</label>
                    <select>
                        <option>Choose patient...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ward</label>
                    <select>
                        <option>Cardiology</option>
                        <option>Pediatrics</option>
                        <option>Orthopedics</option>
                        <option>Emergency</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bed Number</label>
                    <select>
                        <option>C-103 Available</option>
                        <option>C-106 Available</option>
                        <option>C-108 Available</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assignment Date</label>
                    <input type="date">
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea placeholder="Assignment notes..."></textarea>
                </div>

                <button type="button" class="assign-btn">
                    Assign to Bed
                </button>
            </form>

            <div class="recent-section">
                <h4>Recent Assignments</h4>

                <div class="recent-item">
                    <strong>John Smith — C-101</strong>
                    <span>2026-04-15</span>
                </div>

                <div class="recent-item">
                    <strong>Mary Davis — C-102</strong>
                    <span>2026-05-01</span>
                </div>
            </div>
        </div>

    </section>

</main>

</body>
</html>