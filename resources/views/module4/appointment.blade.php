<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments and Treatments</title>
    <link rel="stylesheet" href="{{ asset('css/module4css/module4.css') }}">
</head>
<body>

    @php
        $summary = [
            ['label' => 'Total Appointments', 'value' => '0'],
            ['label' => 'Confirmed', 'value' => '0'],
            ['label' => 'Pending', 'value' => '0'],
            ['label' => 'Checked In', 'value' => '0'],
        ];
    @endphp

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
            <div class="form-card appointment-card">
                <div class="form-title">
                    <h3>Appointment Scheduling</h3>
                    <p>View doctor availability and schedule patient appointments</p>
                </div>

                <section class="date-controls">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Appointment Date</label>
                            <input type="date">
                        </div>
                    </div>

                    <div class="date-actions">
                        <button type="button" class="btn cancel">Previous</button>
                        <button type="button" class="btn submit">Today</button>
                        <button type="button" class="btn cancel">Next</button>
                        <button type="button" class="btn submit">+ New Appointment</button>
                    </div>
                </section>

                <section class="schedule-section">
                    <div class="records-title">
                        <div>
                            <h3>Appointment Schedule</h3>
                            <p>Select a date to view appointment records</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>Appointment Time</th>
                                    <th>Patient No</th>
                                    <th>Patient Name</th>
                                    <th>Assigned Doctor</th>
                                    <th>Appointment Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </section>

                <section class="summary-section">
                    <div>
                        <h4>Schedule Summary</h4>
                        <p>Current totals for the selected date and clinic session.</p>
                    </div>

                    <div class="summary-list">
                        @foreach($summary as $item)
                            <div class="summary-item">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ $item['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
