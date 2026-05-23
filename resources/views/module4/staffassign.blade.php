<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Assignment</title>
    <link rel="stylesheet" href="{{ asset('css/module4.css') }}">
</head>
<body>
    @php
        $summary = [
            ['label' => 'Total Doctors', 'value' => '0'],
            ['label' => 'Total Nurses', 'value' => '0'],
            ['label' => 'Available Staff', 'value' => '0'],
            ['label' => 'Pending Assignments', 'value' => '0'],
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

        <main class="container main-container">
            <div class="form-card">
                <div class="form-title">
                    <h3>Staff Assignment</h3>
                    <p>Assign doctors and nurses to patient treatments</p>
                </div>

                <section class="summary-section">
                    <div>
                        <h4>Staff Summary</h4>
                        <p>Current staff assignment status for today's treatments.</p>
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

                <section class="date-controls main-assignment-layout">
                    <div class="staff-list-section">
                        <div class="records-title">
                            <div>
                                <h3>Staff List</h3>
                                <p>Available staff for diagnosis and treatment assignment</p>
                            </div>
                        </div>

                        <div class="table-wrap">
                            <table class="appointment-table staff-table">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th>Department / Specialization</th>
                                        <th>Staff No</th>
                                        <th>Availability</th>
                                        <th>Active Treatments</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="quick-assignment-card">
                        <div class="records-title">
                            <div>
                                <h3>Quick Assignment</h3>
                                <p>Assign staff to a selected treatment</p>
                            </div>
                        </div>

                        <form method="POST" action="#">
                            @csrf

                            <div class="quick-assignment-form">
                                <div class="form-group">
                                    <label>Select Treatment / Diagnosis</label>
                                    <select name="diagnosis_id">
                                        <option value="">Select treatment</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Assign Doctor</label>
                                    <select name="doctor_staff_no">
                                        <option value="">Select doctor</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Assign Nurse / Assistant</label>
                                    <select name="nurse_staff_no">
                                        <option value="">Select nurse or assistant</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" rows="4" placeholder="Enter assignment notes"></textarea>
                                </div>
                            </div>

                            <div class="date-actions">
                                <button type="submit" class="btn submit">Assign Staff</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="schedule-section">
                    <div class="records-title">
                        <div>
                            <h3>Pending Assignments</h3>
                            <p>Treatments that still need staff assignment</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Appointment Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="schedule-section">
                    <div class="records-title">
                        <div>
                            <h3>Today's Assignments</h3>
                            <p>Staff assigned to today's patient treatments</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Time</th>
                                    <th>Assigned Doctor</th>
                                    <th>Assigned Nurse</th>
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
