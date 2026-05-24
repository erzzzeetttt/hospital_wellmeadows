<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments and Treatments</title>
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
            <a href="{{ route('module4.appointments') }}" class="active">Appointment Scheduling</a>
            <a href="{{ route('module4.treatmentrec') }}">Treatment Recording</a>
        </nav>

        <main class="container">
            <div class="form-card appointment-card">
                <div class="form-title">
                    <h3>Appointment Scheduling</h3>
                    <p>View doctor availability and schedule patient appointments</p>
                </div>

                @if(session('success'))
                <div id="successAlert" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    background: #dcfce7;
                    border: 1px solid #86efac;
                    border-left: 4px solid #16a34a;
                    border-radius: 8px;
                    padding: 16px 20px;
                    max-width: 420px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                ">
                    <div style="flex: 1;">
                        <p style="font-weight: bold; color: #16a34a; margin-bottom: 4px;">Success</p>
                        <p style="color: #166534; font-size: 13px;">{{ session('success') }}</p>
                    </div>
                    <button onclick="document.getElementById('successAlert').style.display='none'" style="
                        background: none;
                        border: none;
                        cursor: pointer;
                        color: #16a34a;
                        font-size: 18px;
                        line-height: 1;
                        padding: 0;
                    ">&times;</button>
                </div>
                <script>
                    setTimeout(function() {
                        const alert = document.getElementById('successAlert');
                        if (alert) alert.style.display = 'none';
                    }, 5000);
                </script>
                @endif

                @if(session('error'))
                <div id="errorAlert" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    background: #fee2e2;
                    border: 1px solid #fca5a5;
                    border-left: 4px solid #dc2626;
                    border-radius: 8px;
                    padding: 16px 20px;
                    max-width: 420px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                ">
                    <div style="flex: 1;">
                        <p style="font-weight: bold; color: #dc2626; margin-bottom: 4px;">Scheduling Error</p>
                        <p style="color: #991b1b; font-size: 13px;">{{ session('error') }}</p>
                    </div>
                    <button onclick="document.getElementById('errorAlert').style.display='none'" style="
                        background: none;
                        border: none;
                        cursor: pointer;
                        color: #dc2626;
                        font-size: 18px;
                        line-height: 1;
                        padding: 0;
                    ">&times;</button>
                </div>
                <script>
                    setTimeout(function() {
                        const alert = document.getElementById('errorAlert');
                        if (alert) alert.style.display = 'none';
                    }, 5000);
                </script>
                @endif
                @if($errors->any())
                    <div class="alert-error">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Schedule New Appointment Form --}}
                <form method="POST" action="{{ route('module4.appointments.store') }}">
                    @csrf
                    <section class="date-controls">
                        <div>
                            <h4>Schedule New Appointment</h4>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Patient</label>
                                    <select name="patient_no" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->patient_no }}"
                                                {{ old('patient_no') == $patient->patient_no ? 'selected' : '' }}>
                                                {{ $patient->patient_no }} — {{ $patient->first_name }} {{ $patient->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Doctor</label>
                                    <select name="staff_no" required>
                                        <option value="">Select Doctor</option>
                                        @foreach($staff as $member)
                                            <option value="{{ $member->staff_no }}"
                                                {{ old('staff_no') == $member->staff_no ? 'selected' : '' }}>
                                                {{ $member->staff_no }} — {{ $member->first_name }} {{ $member->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Date</label>
                                    <input type="date" name="appointment_date"
                                        value="{{ old('appointment_date', $selectedDate) }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Time</label>
                                    <input type="time" name="appointment_time"
                                        value="{{ old('appointment_time') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Examination Room</label>
                                    <input type="text" name="examination_room"
                                        value="{{ old('examination_room') }}"
                                        placeholder="e.g. Room 3A" maxlength="100">
                                </div>

                                <div class="form-group">
                                    <label>Appointment Type *</label>
                                    <select name="appointment_type" required>
                                        <option value="">Select type</option>
                                        <option value="Consultation" {{ old('appointment_type') === 'Consultation' ? 'selected' : '' }}>Consultation</option>
                                        <option value="Follow-up" {{ old('appointment_type') === 'Follow-up' ? 'selected' : '' }}>Follow-up</option>
                                        <option value="Out-patient" {{ old('appointment_type') === 'Out-patient' ? 'selected' : '' }}>Out-patient</option>
                                        <option value="Emergency" {{ old('appointment_type') === 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="date-actions">
                            <button type="submit" class="btn submit">Schedule Appointment</button>
                        </div>
                    </section>
                </form>

                {{-- Date Filter --}}
                <form method="GET" action="{{ route('module4.appointments') }}">
                    <section class="date-controls">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Filter by Date</label>
                                <input type="date" name="appointment_date" value="{{ $selectedDate ?? date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="date-actions">
                            <button type="submit" class="btn submit">View Date</button>
                        </div>
                    </section>
                </form>

                <section class="schedule-section">
                    <div class="records-title">
                        <div>
                            <h3>Appointment Schedule</h3>
                            <p>Showing appointments for {{ $selectedDate }}</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>Appointment Time</th>
                                    <th>Patient No</th>
                                    <th>Patient Name</th>
                                    <th>Staff No</th>
                                    <th>Staff Name</th>
                                    <th>Examination Room</th>
                                    <th>Appointment Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointments as $appt)
                                    @php
                                        $today = date('Y-m-d');
                                        $aptDate = $appt->appointment_date;
                                        if ($aptDate < $today && $appt->status == 'Pending') {
                                            $rowStyle = 'border-left: 4px solid #dc2626;';
                                            $indicator = '<span style="background:#fee2e2;color:#dc2626;padding:2px 6px;border-radius:999px;font-size:10px;font-weight:bold;margin-left:6px;">OVERDUE</span>';
                                        } elseif ($aptDate == $today) {
                                            $rowStyle = 'border-left: 4px solid #f59e0b;';
                                            $indicator = '<span style="background:#fef9c3;color:#854d0e;padding:2px 6px;border-radius:999px;font-size:10px;font-weight:bold;margin-left:6px;">TODAY</span>';
                                        } else {
                                            $rowStyle = 'border-left: 4px solid #16a34a;';
                                            $indicator = '<span style="background:#dcfce7;color:#166534;padding:2px 6px;border-radius:999px;font-size:10px;font-weight:bold;margin-left:6px;">UPCOMING</span>';
                                        }
                                    @endphp
                                    <tr style="{{ $rowStyle }}">
                                        <td>{{ $appt->appointment_time }} {!! $indicator !!}</td>
                                        <td>{{ $appt->patient_no }}</td>
                                        <td>{{ $appt->patient_name }}</td>
                                        <td>{{ $appt->staff_no ?? '&mdash;' }}</td>
                                        <td>{{ $appt->staff_name ?? '&mdash;' }}</td>
                                        <td>{{ $appt->examination_room ?? '&mdash;' }}</td>
                                        <td>{{ $appt->appointment_type ?? '&mdash;' }}</td>
                                        <td>
                                            @if($appt->status == 'Pending')
                                                <span style="background:#fef9c3;color:#854d0e;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:bold;">Pending</span>
                                            @elseif($appt->status == 'Confirmed')
                                                <span style="background:#dcfce7;color:#166534;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:bold;">Confirmed</span>
                                            @elseif($appt->status == 'Checked In')
                                                <span style="background:#dbeafe;color:#1d4ed8;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:bold;">Checked In</span>
                                            @else
                                                <span style="background:#f1f5f9;color:#64748b;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:bold;">{{ $appt->status }}</span>
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;">
                                            @if($appt->status == 'Pending')
                                                <form method="POST" action="{{ route('module4.appointments.status', $appt->appointment_id) }}" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Confirmed">
                                                    <button type="submit" style="background:#16a34a;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:11px;">Confirm</button>
                                                </form>
                                                <form method="POST" action="{{ route('module4.appointments.cancel', $appt->appointment_id) }}" style="display:inline" onsubmit="return confirm('Cancel this appointment?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" style="background:#dc2626;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:11px;">Cancel</button>
                                                </form>
                                            @elseif($appt->status == 'Confirmed')
                                                <form method="POST" action="{{ route('module4.appointments.status', $appt->appointment_id) }}" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Checked In">
                                                    <button type="submit" style="background:#2563eb;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:11px;">Check In</button>
                                                </form>
                                                <form method="POST" action="{{ route('module4.appointments.cancel', $appt->appointment_id) }}" style="display:inline" onsubmit="return confirm('Cancel this appointment?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" style="background:#dc2626;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;font-size:11px;">Cancel</button>
                                                </form>
                                            @elseif($appt->status == 'Checked In')
                                                <span style="background:#dbeafe;color:#1d4ed8;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:bold;">Checked In</span>
                                                <a href="{{ route('module4.treatmentrec') }}" style="background:#7c3aed;color:white;padding:5px 10px;border-radius:5px;font-size:11px;text-decoration:none;display:inline-block;margin-left:4px;">Record Treatment</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align:center;padding:24px;color:#64748b;">
                                            No appointments found for this date.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="display:flex;gap:16px;margin-top:8px;font-size:12px;color:#64748b;">
                        <span><span style="display:inline-block;width:12px;height:12px;background:#dc2626;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Overdue</span>
                        <span><span style="display:inline-block;width:12px;height:12px;background:#f59e0b;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Today</span>
                        <span><span style="display:inline-block;width:12px;height:12px;background:#16a34a;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Upcoming</span>
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
