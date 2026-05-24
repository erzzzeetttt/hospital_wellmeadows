<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Recording</title>
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
            <a href="{{ route('module4.treatmentrec') }}" class="active">Treatment Recording</a>
        </nav>

        <main class="container">
            <div class="form-card">
                <div class="form-title">
                    <h3>Treatment Recording</h3>
                    <p>Record patient treatment details for a completed appointment</p>
                </div>

                @if(session('success'))
                    <div style="background:#dcfce7;color:#166534;padding:12px 26px;border-bottom:1px solid #e2e8f0;">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background:#fee2e2;color:#991b1b;padding:12px 26px;border-bottom:1px solid #e2e8f0;">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div style="background:#fee2e2;color:#991b1b;padding:12px 26px;border-bottom:1px solid #e2e8f0;">
                        <strong>Please fix these errors:</strong>
                        <ul style="margin:8px 0 0 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('module4.treatmentrec.store') }}">
                    @csrf

                    {{-- Patient Information --}}
                    <section class="date-controls" style="margin-bottom:24px;padding:28px;">
                        <div>
                            <h4 style="font-size:15px;font-weight:700;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #e2e8f0;">
                                Patient Information
                            </h4>

                            <div class="form-group full" style="margin-bottom:20px;background:#f0f7ff;padding:16px;border-radius:8px;">
                                <label style="font-weight:600;margin-bottom:8px;display:block;">Select Appointment *</label>
                                <select name="appointment_id" id="appointmentSelect" required onchange="fillFromAppointment(this)">
                                    <option value="">-- Select an appointment --</option>
                                    @foreach($appointments as $apt)
                                        <option value="{{ $apt->appointment_id }}"
                                            data-patient="{{ $apt->patient_no }}"
                                            data-patient-name="{{ $apt->patient_name }}"
                                            data-staff="{{ $apt->staff_no }}"
                                            data-staff-name="{{ $apt->staff_name }}"
                                            data-date="{{ $apt->appointment_date }}"
                                            {{ old('appointment_id') == $apt->appointment_id ? 'selected' : '' }}>
                                            #{{ $apt->appointment_id }} — {{ $apt->patient_name }} | {{ $apt->appointment_date }} {{ $apt->appointment_time }} | {{ $apt->appointment_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <input type="hidden" name="patient_no" id="patientNo" value="{{ old('patient_no') }}">
                            <input type="hidden" name="staff_no" id="staffNo" value="{{ old('staff_no') }}">

                            <div class="form-grid">
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Patient</label>
                                    <input type="text" id="patientDisplay" readonly placeholder="Auto-filled from appointment"
                                        style="background:#f8fafc;color:#64748b;">
                                    <small style="color:#94a3b8;font-size:11px;margin-top:4px;display:block;">Auto-filled</small>
                                </div>
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Attending Staff</label>
                                    <input type="text" id="staffDisplay" readonly placeholder="Auto-filled from appointment"
                                        style="background:#f8fafc;color:#64748b;">
                                    <small style="color:#94a3b8;font-size:11px;margin-top:4px;display:block;">Auto-filled</small>
                                </div>
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Treatment Date *</label>
                                    <input type="date" name="diagnosis_date" id="treatmentDate"
                                        value="{{ old('diagnosis_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>
                    </section>

                    <script>
                    function fillFromAppointment(select) {
                        const option = select.options[select.selectedIndex];
                        document.getElementById('patientNo').value = option.dataset.patient || '';
                        document.getElementById('staffNo').value = option.dataset.staff || '';
                        document.getElementById('patientDisplay').value = option.dataset.patientName || '';
                        document.getElementById('staffDisplay').value = option.dataset.staffName || '';

                        // Set treatment date to TODAY not appointment date
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('treatmentDate').value = today;
                    }
                    // Restore display fields on validation error (old values in hidden inputs)
                    (function() {
                        const sel = document.getElementById('appointmentSelect');
                        if (sel && sel.value) fillFromAppointment(sel);
                    })();
                    </script>

                    {{-- Diagnosis / Findings --}}
                    <section class="date-controls" style="margin-bottom:24px;padding:28px;border-top:1px solid #e2e8f0;padding-top:24px;">
                        <div>
                            <h4 style="font-size:15px;font-weight:700;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #e2e8f0;">
                                Diagnosis / Findings
                            </h4>

                            <div class="form-group" style="margin-bottom:20px;">
                                <label style="font-weight:600;margin-bottom:8px;display:block;">Diagnosis Details *</label>
                                <textarea name="diagnosis_details" required
                                    placeholder="Enter diagnosis or clinical findings">{{ old('diagnosis_details') }}</textarea>
                            </div>
                        </div>
                    </section>

                    {{-- Treatment Details --}}
                    <section class="date-controls" style="margin-bottom:24px;padding:28px;border-top:1px solid #e2e8f0;padding-top:24px;">
                        <div>
                            <h4 style="font-size:15px;font-weight:700;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #e2e8f0;">
                                Treatment Details
                            </h4>

                            <div class="form-grid">
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Treatment Type *</label>
                                    <select name="treatment_type" required>
                                        <option value="">Select type</option>
                                        <option value="Medication" {{ old('treatment_type') === 'Medication' ? 'selected' : '' }}>Medication</option>
                                        <option value="Surgical" {{ old('treatment_type') === 'Surgical' ? 'selected' : '' }}>Surgical</option>
                                        <option value="Non-Surgical" {{ old('treatment_type') === 'Non-Surgical' ? 'selected' : '' }}>Non-Surgical</option>
                                        <option value="Diagnosis Only" {{ old('treatment_type') === 'Diagnosis Only' ? 'selected' : '' }}>Diagnosis Only</option>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Method</label>
                                    <select name="method">
                                        <option value="">Select method</option>
                                        <option value="Oral" {{ old('method') === 'Oral' ? 'selected' : '' }}>Oral</option>
                                        <option value="IV" {{ old('method') === 'IV' ? 'selected' : '' }}>IV</option>
                                        <option value="Injection" {{ old('method') === 'Injection' ? 'selected' : '' }}>Injection</option>
                                        <option value="Topical" {{ old('method') === 'Topical' ? 'selected' : '' }}>Topical</option>
                                        <option value="Inhalation" {{ old('method') === 'Inhalation' ? 'selected' : '' }}>Inhalation</option>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Treatment Given</label>
                                    <textarea name="treatment_given" rows="4" placeholder="Describe treatment provided">{{ old('treatment_given') }}</textarea>
                                </div>

                                <div class="form-group" style="margin-bottom:20px;">
                                    <label style="font-weight:600;margin-bottom:8px;display:block;">Remarks / Notes</label>
                                    <textarea name="remarks" rows="4" placeholder="Additional notes">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Actions --}}
                    <section class="summary-section" style="margin-top:32px;">
                        <div>
                            <h4 style="font-weight:700;">Actions</h4>
                            <p>Save the record or clear the form before entering a new record.</p>
                        </div>

                        <div class="date-actions">
                            <button type="submit" class="btn submit" style="padding:14px 28px;">Save Record</button>
                            <button type="reset" class="btn cancel">Clear Form</button>
                        </div>
                    </section>
                </form>

                {{-- Treatment Records Table --}}
                <section class="schedule-section" style="margin-top:40px;border-top:2px solid #e2e8f0;padding-top:32px;">
                    <div class="records-title">
                        <div>
                            <h3>Treatment Records</h3>
                            <p>All recorded patient treatments</p>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-bottom:16px;">
                        <div class="form-group">
                            <label style="font-weight:600;margin-bottom:8px;display:block;">Search Patient</label>
                            <input type="text" id="searchPatient" placeholder="Enter patient name or number" oninput="filterTreatments()">
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600;margin-bottom:8px;display:block;">Filter by Date</label>
                            <input type="date" id="filterDate" onchange="filterTreatments()">
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="appointment-table" id="treatmentTable">
                            <thead>
                                <tr style="background:#f1f5f9;">
                                    <th>Treatment Date</th>
                                    <th>Patient Name</th>
                                    <th>Staff Name</th>
                                    <th>Diagnosis</th>
                                    <th>Treatment Type</th>
                                    <th>Treatment Given</th>
                                    <th>Method</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($treatments as $record)
                                    <tr>
                                        <td>{{ $record->treatment_date }}</td>
                                        <td>{{ $record->patient_name }}</td>
                                        <td>{{ $record->staff_name ?? '&mdash;' }}</td>
                                        <td style="max-width:200px;white-space:pre-wrap;">{{ Str::limit($record->diagnosis_details, 80) }}</td>
                                        <td>{{ $record->treatment_type ?? '&mdash;' }}</td>
                                        <td style="max-width:200px;white-space:pre-wrap;">{{ Str::limit($record->treatment_given, 80) }}</td>
                                        <td>{{ $record->method ?? '&mdash;' }}</td>
                                        <td style="white-space:nowrap;">
                                            <a href="{{ route('module4.treatmentrec.edit', $record->diagnosis_id) }}" class="btn submit" style="padding:4px 10px;font-size:12px;text-decoration:none;">Edit</a>
                                            <form method="POST" action="{{ route('module4.treatment.delete', $record->treatment_id) }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn cancel" style="padding:4px 10px;font-size:12px;"
                                                    onclick="return confirm('Delete this record?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" style="text-align:center;padding:24px;color:#64748b;">
                                            No treatment records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
    function filterTreatments() {
        const search = document.getElementById('searchPatient').value.toLowerCase();
        const date = document.getElementById('filterDate').value;
        const rows = document.querySelectorAll('#treatmentTable tbody tr');

        rows.forEach(row => {
            const patientName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const patientNo = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const rowDate = row.querySelector('td:nth-child(4)')?.textContent || '';

            const matchSearch = !search || patientName.includes(search) || patientNo.includes(search);
            const matchDate = !date || rowDate.includes(date);

            row.style.display = matchSearch && matchDate ? '' : 'none';
        });
    }
    </script>
</body>
</html>
