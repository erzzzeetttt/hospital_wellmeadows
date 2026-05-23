<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment History</title>
    <link rel="stylesheet" href="{{ asset('css/module4css/module4.css') }}">
</head>
<body>
    <div class="page module4-page">
        <header class="header">
            <div>
                <h2>WellMeadows Hospital</h2>
                <p>Appointments and Treatments</p>
            </div>
            <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
        </header>

        <nav class="sub-nav">
            <a href="{{ route('module4.appointments') }}">Appointment Scheduling</a>
            <a href="{{ route('module4.treatmentrec') }}">Treatment Recording</a>
            <a href="{{ route('module4.treatmenthistory') }}">Treatment History</a>
            <a href="{{ route('module4.staffassign') }}">Staff Assignment</a>
        </nav>

        <main class="container">
            <div class="form-card">
                <div class="form-title">
                    <h3>Treatment History</h3>
                    <p>View patient diagnosis and treatment records</p>
                </div>

                <section class="date-controls">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Search Patient</label>
                            <input type="text" placeholder="Enter patient name or number">
                        </div>

                        <div class="form-group">
                            <label>Filter by Date</label>
                            <input type="date">
                        </div>

                        <div class="form-group">
                            <label>Filter by Doctor</label>
                            <select>
                                <option value="">All doctors</option>
                            </select>
                        </div>
                    </div>

                    <div class="date-actions">
                        <button type="button" class="btn submit">Search</button>
                    </div>
                </section>

                <section class="schedule-section">
                    <div class="records-title">
                        <div>
                            <h3>Treatment History Records</h3>
                            <p>Diagnosis records will appear here when available</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>Diagnosis ID</th>
                                    <th>Patient No</th>
                                    <th>Patient Name</th>
                                    <th>Doctor / Staff</th>
                                    <th>Diagnosis Date</th>
                                    <th>Diagnosis Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
