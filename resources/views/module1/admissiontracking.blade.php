<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Tracking | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module1css/admissiontracking.css') }}">
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
    <a href="{{ route('admission-tracking.index') }}" class="active">Admission Tracking</a>
</nav>

<main class="admission-container">

    <section class="summary-grid">

        <div class="summary-card">
            <div>
                <span>Total Admissions</span>
                <h3>{{ $totalAdmissions }}</h3>
                <p>All recorded admissions</p>
            </div>
        </div>

        <div class="summary-card">
            <div>
                <span>Currently Admitted</span>
                <h3>{{ $currentlyAdmitted }}</h3>
                <p>Active patients</p>
            </div>
        </div>

        <div class="summary-card">
            <div>
        <span>Pending Discharge</span>
        <h3>{{ $pendingDischarge }}</h3>
        <p>For discharge processing</p>
                </div>
</div>

        <div class="summary-card">
            <div>
                <span>Recent Discharges</span>
                <h3>{{ $recentDischargesCount }}</h3>
                <p>Completed discharges</p>
            </div>
        </div>

    </section>

    <section class="tracking-grid">

    <div class="panel-card">

        <div class="panel-header">
            <div>
                <h3>Active Admissions</h3>
                <p>Patients currently admitted in the hospital</p>
            </div>

            <button type="button"
                    class="primary-btn new-admission-btn"
                    id="openAdmissionModal">
                + New Admission
            </button>
        </div>

        <div class="admission-list">

            @forelse($activeAdmissions as $admission)

                <div class="admission-item">
                    <div class="admission-top">
                        <div>
                            <h4>{{ $admission->first_name }} {{ $admission->last_name }}</h4>
                            <p>ID: {{ $admission->patient_no }} • Admission: A{{ $admission->admission_id }}</p>
                        </div>

                        <span class="status-badge admitted">Admitted</span>
                    </div>

                    <div class="admission-info">
                        <div>
                            <label>Ward & Bed</label>
                            <strong>
                                @if($admission->ward_name && $admission->bed_number)
                                    {{ $admission->ward_name }} - Bed {{ $admission->bed_number }}
                                @else
                                    Not assigned yet
                                @endif
                            </strong>
                        </div>

                        <div>
                            <label>Admission Date</label>
                            <strong>{{ $admission->date_admitted }}</strong>
                        </div>

                        <div>
                            <label>Expected Discharge</label>
                            <strong>{{ $admission->expected_leave_date }}</strong>
                        </div>

                        <div>
                            <label>Status</label>
                            <strong>{{ $admission->status }}</strong>
                        </div>
                    </div>

                    <div class="admission-actions">
                        <button type="button" class="secondary-btn">View Details</button>
                        <form action="{{ route('admission-tracking.discharge', $admission->admission_id) }}"
                        method="POST">

                        @csrf
                        @method('PUT')

    <input type="hidden"
           name="discharge_date"
           value="{{ now()->format('Y-m-d') }}">

    <button type="submit" class="primary-btn">
        Process Discharge
    </button>

</form>
                    </div>
                </div>

            @empty

                <div class="empty-note">
                    No admitted patients yet.
                </div>

            @endforelse

        </div>

    </div>

    <div class="panel-card">

        <div class="panel-header">
            <div>
                <h3>Recent Discharges</h3>
                <p>Recently discharged patients</p>
            </div>
        </div>

        <div class="discharge-list">

    @forelse($recentDischarges as $discharge)

    <div class="discharge-item">
        <div class="discharge-top">
            <div>
                <h4>{{ $discharge->first_name }} {{ $discharge->last_name }}</h4>
                <p>ID: {{ $discharge->patient_no }} • Admission: A{{ $discharge->admission_id }}</p>
            </div>

            <span class="status-badge discharged">Discharged</span>
        </div>

        <div class="discharge-info">
            <div>
                <label>Admission Date</label>
                <strong>{{ $discharge->date_admitted }}</strong>
            </div>

            <div>
                <label>Discharge Date</label>
                <strong>{{ $discharge->discharge_date }}</strong>
            </div>
        </div>
    </div>

    @empty

    <div class="empty-note">
        No discharged patients yet.
    </div>

    @endforelse

</div>

    </div>

</section>
<div id="admissionModal" class="modal-overlay">

    <div class="modal-box">

        <div class="modal-header">
            <h3>New Admission</h3>
        </div>

        <form action="{{ route('admission-tracking.store') }}"
              method="POST">

            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label>Patient</label>

                    <select name="patient_no" required>
                        <option value="">Select patient</option>

                        @foreach($patients as $patient)
                            <option value="{{ $patient->patient_no }}">
                                {{ $patient->patient_no }}
                                -
                                {{ $patient->first_name }}
                                {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Date Admitted</label>

                    <input type="date"
                           name="date_admitted"
                           required>
                </div>

                <div class="form-group">
                    <label>Expected Leave Date</label>

                    <input type="date"
                           name="expected_leave_date"
                           required>
                </div>

            </div>

            <div class="modal-actions">

                <button type="button"
                        class="secondary-btn"
                        id="closeAdmissionModal">
                    Cancel
                </button>

                <button type="submit"
                        class="primary-btn">
                    Admit Patient
                </button>

            </div>

        </form>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("admissionModal");

    document.getElementById("openAdmissionModal")
        .addEventListener("click", function () {
            modal.style.display = "flex";
        });

    document.getElementById("closeAdmissionModal")
        .addEventListener("click", function () {
            modal.style.display = "none";
        });

});
</script>

</body>
</html>