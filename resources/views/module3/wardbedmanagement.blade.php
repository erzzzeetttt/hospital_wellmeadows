<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ward & Bed Management | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module3css/wardbedmanagement.css') }}">
</head>
<body>
    <header class="module-header">
        <div>
            <h1>WellMeadows Hospital</h1>
            <p>Ward &amp; Bed Management System</p>
        </div>

        <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
    </header>

    <main class="ward-page">
        <input type="radio" name="ward-tab" id="tab-all-wards" @checked($activeTab === 'all-wards')>
        <input type="radio" name="ward-tab" id="tab-add-ward" @checked($activeTab === 'add-ward')>
        <input type="radio" name="ward-tab" id="tab-assign-bed" @checked($activeTab === 'assign-bed')>
        <input type="radio" name="ward-tab" id="tab-bed-availability" @checked($activeTab === 'bed-availability')>

        <nav class="ward-tabs" aria-label="Ward management sections">
            <label for="tab-all-wards">All Wards</label>
            <label for="tab-add-ward">Add Ward</label>
            <label for="tab-assign-bed">Assign Bed</label>
            <label for="tab-bed-availability">Bed Availability</label>
        </nav>

        @if(session('success'))
            <div class="flash-message">{{ session('success') }}</div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="error-message">
                <strong>Please check the form and try again.</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="tab-panel all-wards-panel">
            <div class="content-card">
                <div class="card-header">
                    <h2>Available Wards</h2>
                    <p>Total of <strong>{{ $wards->count() }}</strong> wards registered in the system</p>
                </div>

                <div class="table-wrap">
                    <table class="wards-table">
                        <thead>
                            <tr>
                                <th>Ward<br>ID</th>
                                <th>Ward<br>Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Charge<br>Nurse</th>
                                <th>Total<br>Beds</th>
                                <th>Vacant</th>
                                <th>Occupied</th>
                                <th>Maintenance</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Wards and bed counts are loaded by WardBedManagementController from the database. --}}
                            @forelse($wards as $ward)
                                <tr>
                                    <td>W{{ str_pad((string) $ward->ward_id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td><strong>{{ $ward->ward_name }}</strong></td>
                                    <td>{{ $ward->ward_type }}</td>
                                    <td>{{ $ward->location }}</td>
                                    <td>{{ $ward->charge_nurse }}</td>
                                    <td class="number">{{ $ward->beds_count }}</td>
                                    <td class="number vacant">{{ $ward->vacant_beds_count }}</td>
                                    <td class="number occupied">{{ $ward->occupied_beds_count }}</td>
                                    <td class="number maintenance">{{ $ward->maintenance_beds_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="empty-cell">No wards have been added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="tab-panel add-ward-panel">
            <form class="content-card form-card" method="POST" action="{{ route('ward-bed-management.wards.store') }}">
                @csrf

                <div class="card-header">
                    <h2>Add New Ward</h2>
                    <p>Enter ward details to register a new ward in the system</p>
                </div>

                {{-- This form creates one ward record and the matching number of available bed records. --}}
                <div class="form-section">
                    <h3>Ward Information</h3>

                    <div class="form-grid">
                        <label>
                            <span>Ward Name <em>*</em></span>
                            <input type="text" name="ward_name" value="{{ old('ward_name') }}" placeholder="e.g. Grampian" required>
                        </label>

                        <label>
                            <span>Ward Type <em>*</em></span>
                            <select name="ward_type" required>
                                <option value="">Select ward type</option>
                                <option @selected(old('ward_type') === 'Orthopaedic')>Orthopaedic</option>
                                <option @selected(old('ward_type') === 'Cardiology')>Cardiology</option>
                                <option @selected(old('ward_type') === 'General Surgery')>General Surgery</option>
                                <option @selected(old('ward_type') === 'Medical')>Medical</option>
                                <option @selected(old('ward_type') === 'Surgical')>Surgical</option>
                            </select>
                        </label>

                        <label>
                            <span>Location <em>*</em></span>
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Block A, Floor 2" required>
                        </label>

                        <label>
                            <span>Total Bed Capacity <em>*</em></span>
                            <input type="number" name="total_beds" value="{{ old('total_beds') }}" min="1" placeholder="e.g. 12" required>
                        </label>
                    </div>
                </div>

                <div class="form-section contact-section">
                    <h3>Contact Details</h3>

                    <div class="form-grid">
                        {{-- Charge nurses come from staff records where position is Charge Nurse. --}}
                        <label>
                            <span>Charge Nurse <em>*</em></span>
                            <select name="charge_nurse_staff_no" required>
                                <option value="">Select charge nurse</option>
                                @foreach($chargeNurses as $chargeNurse)
                                    <option value="{{ $chargeNurse->staff_no }}" @selected((string) old('charge_nurse_staff_no') === (string) $chargeNurse->staff_no)>
                                        {{ $chargeNurse->first_name }} {{ $chargeNurse->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Phone / Extension</span>
                            <input type="text" name="telephone_extension" value="{{ old('telephone_extension') }}" placeholder="e.g. ext. 2201">
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-light">Clear</button>
                    <button type="submit" class="btn btn-primary">Add Ward</button>
                </div>
            </form>
        </section>

        <section class="tab-panel assign-bed-panel">
            <form class="content-card form-card" method="POST" action="{{ route('ward-bed-management.assign-bed.store') }}">
                @csrf

                <div class="card-header">
                    <h2>Assign Bed to Patient</h2>
                    <p>Select a ward and available bed to assign to an admitted patient</p>
                </div>

                {{-- Ward selection filters the bed dropdown to beds that are still available in that ward. --}}
                <div class="form-section">
                    <h3>Bed Selection</h3>

                    <div class="form-grid">
                        <label>
                            <span>Ward <em>*</em></span>
                            <select name="ward_id" id="wardSelect" required>
                                <option value="">Select a ward</option>
                                @foreach($wards as $ward)
                                    <option value="{{ $ward->ward_id }}" @selected((string) old('ward_id') === (string) $ward->ward_id)>
                                        {{ $ward->ward_name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Available Bed <em>*</em></span>
                            <select name="bed_id" id="bedSelect" required>
                                <option value="">Select a bed</option>
                                @foreach($availableBeds as $bed)
                                    <option value="{{ $bed->bed_id }}" data-ward="{{ $bed->ward_id }}" @selected((string) old('bed_id') === (string) $bed->bed_id)>
                                        {{ $bed->ward->ward_name }} - Bed {{ $bed->bed_number }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <div class="form-section contact-section">
                    <h3>Patient Details</h3>

                    <div class="form-grid">
                        <label>
                            <span>Admitted Patient <em>*</em></span>
                            <select name="patient_no" id="patientSelect" required>
                                <option value="">Select admitted patient</option>
                                @foreach($admittedPatients as $admission)
                                    <option
                                        value="{{ $admission->patient_no }}"
                                        data-doctor="{{ $admission->patient?->doctor?->fullname ?? 'No doctor recorded' }}"
                                        data-admission-date="{{ $admission->date_admitted }}"
                                        @selected(old('patient_no') === $admission->patient_no)
                                    >
                                        {{ $admission->patient_no }} - {{ $admission->patient?->first_name }} {{ $admission->patient?->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Allocation Date <em>*</em></span>
                            <input type="date" name="allocation_date" value="{{ old('allocation_date', now()->format('Y-m-d')) }}" required>
                        </label>

                        <label>
                            <span>Consulting Doctor</span>
                            <input type="text" id="doctorDisplay" placeholder="Select a patient" readonly>
                        </label>

                        <label>
                            <span>Admission Date</span>
                            <input type="text" id="admissionDateDisplay" placeholder="Select a patient" readonly>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-light">Clear</button>
                    <button type="submit" class="btn btn-primary">Assign Bed</button>
                </div>
            </form>
        </section>

        <section class="tab-panel bed-availability-panel">
            <div class="stats-grid">
                <article class="stat-card total">
                    <span>Total Beds</span>
                    <strong>{{ $stats['totalBeds'] }}</strong>
                </article>
                <article class="stat-card vacant-bg">
                    <span>Vacant</span>
                    <strong>{{ $stats['vacantBeds'] }}</strong>
                </article>
                <article class="stat-card occupied-bg">
                    <span>Occupied</span>
                    <strong>{{ $stats['occupiedBeds'] }}</strong>
                </article>
                <article class="stat-card maintenance-bg">
                    <span>Maintenance</span>
                    <strong>{{ $stats['maintenanceBeds'] }}</strong>
                </article>
            </div>

            <div class="content-card availability-card">
                <div class="availability-header">
                    <div>
                        <h2>Bed Availability by Ward</h2>
                        <p>View and filter bed status across all wards</p>
                    </div>

                    <form class="ward-filter" method="GET" action="{{ route('ward-bed-management.index') }}">
                        <input type="hidden" name="tab" value="bed-availability">
                        <span>Ward:</span>
                        <select name="ward_id" onchange="this.form.submit()">
                            <option value="">All Wards</option>
                            @foreach($wards as $ward)
                                <option value="{{ $ward->ward_id }}" @selected($selectedWardId === $ward->ward_id)>
                                    {{ $ward->ward_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Availability is grouped by ward and can be filtered with the Ward dropdown above. --}}
                @forelse($availabilityWards as $ward)
                    <div class="ward-block">
                        <div class="ward-block-title">
                            <h3>{{ $ward->ward_name }}</h3>
                            <p>{{ $ward->ward_type }} <span>&middot;</span> {{ $ward->location }}</p>
                            <div class="ward-counts">
                                <span class="vacant">{{ $ward->vacant_beds_count }} Vacant</span>
                                <span class="occupied">{{ $ward->occupied_beds_count }} Occupied</span>
                                <span class="maintenance">{{ $ward->maintenance_beds_count }} Maintenance</span>
                            </div>
                        </div>

                        <table class="beds-table">
                            <thead>
                                <tr>
                                    <th>Bed No.</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Patient</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ward->beds as $bed)
                                    <tr>
                                        <td>{{ $bed->bed_number }}</td>
                                        <td>Standard</td>
                                        <td>
                                            <span class="badge {{ $bed->status === 'Available' ? 'vacant-badge' : strtolower($bed->status) . '-badge' }}">
                                                {{ $bed->status === 'Available' ? 'Vacant' : $bed->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($bed->activeAllocation?->patient)
                                                {{ $bed->activeAllocation->patient->first_name }} {{ $bed->activeAllocation->patient->last_name }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="empty-availability">No bed availability records to display yet.</div>
                @endforelse
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wardSelect = document.getElementById('wardSelect');
            const bedSelect = document.getElementById('bedSelect');
            const patientSelect = document.getElementById('patientSelect');
            const doctorDisplay = document.getElementById('doctorDisplay');
            const admissionDateDisplay = document.getElementById('admissionDateDisplay');

            function filterBedsByWard() {
                const selectedWard = wardSelect.value;

                Array.from(bedSelect.options).forEach(function (option) {
                    if (! option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = option.dataset.ward !== selectedWard;
                });

                if (bedSelect.selectedOptions[0]?.hidden) {
                    bedSelect.value = '';
                }
            }

            function showSelectedPatientDetails() {
                const option = patientSelect.selectedOptions[0];

                doctorDisplay.value = option?.dataset.doctor || '';
                admissionDateDisplay.value = option?.dataset.admissionDate || '';
            }

            wardSelect.addEventListener('change', filterBedsByWard);
            patientSelect.addEventListener('change', showSelectedPatientDetails);

            filterBedsByWard();
            showSelectedPatientDetails();
        });
    </script>
</body>
</html>
