<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Records | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module1css/medicalrecords.css') }}">
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
    <a href="{{ route('medical-records.index') }}" class="active">Medical Records</a>
    <a href="#">Ward Assignment</a>
    <a href="#">Admission Tracking</a>
</nav>

<main class="medical-container">

    <section class="patient-panel">
        <h3>Patients</h3>

        <input type="text" class="search-box" placeholder="Search patients...">

        <div class="patient-list">
    @forelse($patients as $patient)

        <a href="{{ route('medical-records.show', $patient->patient_no) }}"
           class="patient-item">

            <div class="avatar">
                {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}
            </div>

            <div>
                <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
                <p>{{ $patient->patient_no }} • {{ $patient->gender }}</p>
            </div>

        </a>

    @empty
        <p class="empty">No patients found.</p>
    @endforelse
</div>
    </section>

    <section class="record-panel">
        @if (session('success'))
    <div style="background:#dcfce7; color:#166534; padding:12px; margin:15px;">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div style="background:#fee2e2; color:#991b1b; padding:12px; margin:15px;">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
        <div class="record-header">
            <div>
                <h3>Medical Records</h3>
                <div class="selected-patient">
    <span>Selected Patient</span>

    <h4>
        @isset($selectedPatient)
            {{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }}
        @else
            No patient selected
        @endisset
    </h4>
</div>
            </div>

            <button type="button" class="add-btn" id="openAddRecordBtn">
    + Add Record
</button>
        </div>

        <div class="info-grid">
            
            <div>
                <span>Current Ward</span>
                <strong>Not assigned</strong>
            </div>

            <div>
                <span>Admission Date</span>
                <strong>Not admitted</strong>
            </div>
        </div>

       <div class="timeline">

    @isset($medications)
        @forelse($medications as $med)
            <div class="medication-card">
    <div class="medication-card-header">
        <div>
            <h4>{{ $med->drug_name }}</h4>
            <p>Medication Record</p>
        </div>

        <span>{{ $med->start_date }}</span>
    </div>

    <div class="medication-details">
        <div>
            <label>Dosage</label>
            <strong>{{ $med->dosage }}</strong>
        </div>

        <div>
            <label>Frequency</label>
            <strong>{{ $med->frequency }}</strong>
        </div>

        <div>
            <label>End Date</label>
            <strong>{{ $med->end_date ?? 'Ongoing' }}</strong>
        </div>
    </div>

    <div class="medication-card-actions">
        <button
    type="button"
    class="edit-med-btn"
    onclick="openEditMedicationModal(
        '{{ $med->medication_id }}',
        '{{ $med->drug_id }}',
        '{{ $med->dosage }}',
        '{{ $med->frequency }}',
        '{{ $med->start_date }}',
        '{{ $med->end_date }}'
    )">
    Edit Record
</button>
    </div>

</div>

        @empty
            <p>No medication records for this patient yet.</p>
        @endforelse
    @else
        <div class="empty-record-state">
    <h3>No Patient Selected</h3>

    <p>
        Select a patient from the left panel to view
        medication records and medical information.
    </p>
</div>
    @endisset

</div>
            <div class="section-title">
            <h3>Diagnosis Records</h3>
    <p>
        Diagnosis records will be available once staff module is integrated.
    </p>
                </div>

                <div class="placeholder-card">
    <strong>No diagnosis records yet.</strong>

    <p>
        This section is reserved for future diagnosis entries and staff integration.
    </p>
                    </div>
                </div>
            </div>

        </div>


        </div>

    </section>

</main>

<div id="addRecordModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Medication Record</h3>
        </div>

        <form action="{{ route('medical-records.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Patient</label>
                    <select name="patient_no">
                        <option value="">Select patient</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->patient_no }}">
                                {{ $patient->patient_no }} - {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
    <label>Drug</label>
    <select name="drug_id" required>
        <option value="">Select drug</option>

        @foreach($drugs as $drug)
            <option value="{{ $drug->drug_id }}">
                {{ $drug->drug_name }}
            </option>
        @endforeach
    </select>
</div>

                <div class="form-group">
                    <label>Dosage</label>
                    <input type="text" name="dosage" placeholder="e.g. 500mg">
                </div>

                <div class="form-group">
                    <label>Frequency</label>
                    <input type="text" name="frequency" placeholder="e.g. 3 times a day">
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date">
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="cancel-btn closeAddRecordModal">Cancel</button>
                <button type="submit" class="save-btn">Save Record</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("addRecordModal");
        const openBtn = document.getElementById("openAddRecordBtn");
        const closeBtns = document.querySelectorAll(".closeAddRecordModal");

        openBtn.addEventListener("click", function () {
            modal.style.display = "flex";
        });

        closeBtns.forEach(function (btn) {
            btn.addEventListener("click", function () {
                modal.style.display = "none";
            });
        });
    });
</script>

    <div id="editMedicationModal" class="modal-overlay">
        <div class="modal-box">

        <div class="modal-header">
            <h3>Edit Medication Record</h3>
        </div>

        <form id="editMedicationForm" method="POST">
         @csrf
        @method('PUT')

            <input type="hidden" id="edit_medication_id">

            <div class="form-grid">

                <div class="form-group">
                    <label>Drug</label>

                   <select id="edit_drug_id" name="drug_id">
                        @foreach($drugs as $drug)
                            <option value="{{ $drug->drug_id }}">
                                {{ $drug->drug_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="form-group">
                    <label>Dosage</label>
                    <input type="text" id="edit_dosage" name="dosage">
                </div>

                <div class="form-group">
                    <label>Frequency</label>
                    <input type="text" id="edit_frequency" name="frequency">
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" id="edit_start_date" name="start_date">
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" id="edit_end_date" name="end_date">
                </div>

            </div>

            <div class="modal-actions">

                <button type="button"
                        class="cancel-btn"
                        onclick="closeEditMedicationModal()">
                    Cancel
                </button>

                <button type="submit"
                        class="save-btn">
                    Update Record
                </button>

            </div>

        </form>

    </div>

</div>

<script>
function openEditMedicationModal(
    medicationId,
    drugId,
    dosage,
    frequency,
    startDate,
    endDate
) {
    document.getElementById('editMedicationModal').style.display = 'flex';

    document.getElementById('edit_medication_id').value = medicationId;
    document.getElementById('edit_drug_id').value = drugId;
    document.getElementById('edit_dosage').value = dosage;
    document.getElementById('edit_frequency').value = frequency;
    document.getElementById('edit_start_date').value = startDate;
    document.getElementById('edit_end_date').value = endDate;
    document.getElementById('editMedicationForm').action = `/medical-records/${medicationId}`;
}

function closeEditMedicationModal() {
    document.getElementById('editMedicationModal').style.display = 'none';
}
</script>

        </body>
</html>
