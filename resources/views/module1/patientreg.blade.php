<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Patient | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module1css/patientreg.css') }}">
</head>
           
<body>
<div class="page">

    <header class="header">
        <div>
            <h2>WellMeadows Hospital</h2>
            <p>Patient Management System</p>
        </div>

        <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
    </header>

    <nav class="sub-nav">

    <a href="{{ route('patients.create') }}" class="active">Patient Registration</a>
    <a href="{{ route('medical-records.index') }}">Medical Records</a>
    <a href="{{ route('admission-tracking.index') }}">Admission Tracking</a>

</nav>

    <main class="container">

        <div class="form-card">
            <div class="form-title">
                <h3>Register New Patient</h3>
                <p>Enter patient information to create a new record</p>
            </div>

            @if (session('error'))
                <div class="alert-error">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

    @if ($errors->any())
    <div style="background:#fee2e2; color:#991b1b; padding:12px 20px; margin:20px 26px; border-radius:6px;">
        <strong>Please fix these errors:</strong>
        <ul style="margin-left:20px; margin-top:8px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

            <form action="{{ route('patients.store') }}" method="POST">
                @csrf

                <h4>Personal Information</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" placeholder="Enter last name">
                    </div>

                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth">
                    </div>

                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender">
                            <option value="">Select gender</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="phone_no" placeholder="+63 900 000 0000">
                    </div>

                    <div class="form-group">
                        <label>Marital Status</label>
                        <select name="marital_status">
                            <option value="">Select status</option>
                            <option>Single</option>
                            <option>Married</option>
                            <option>Widowed</option>
                            <option>Divorced</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Address *</label>
                    <input type="text" name="address" placeholder="Enter complete address">
                </div>

                <h4>Doctor Assignment</h4>

                <div class="form-group full">
                    <label>Local Doctor *</label>
                    <select name="doctor_id" required>
    <option value="">Select doctor</option>

    @foreach($doctors as $doctor)
        <option value="{{ $doctor->doctor_id }}">
            {{ $doctor->fullname }}
        </option>
    @endforeach
</select>
                </div>

                <h4>Next of Kin Information</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="kin_fullname" placeholder="Emergency contact name">
                    </div>

                    <div class="form-group">
                        <label>Relationship *</label>
                        <input type="text" name="relationshiptopatient" placeholder="e.g. Mother, Father">
                    </div>

                    <div class="form-group">
                        <label>Telephone *</label>
                        <input type="text" name="kin_telno" placeholder="+63 900 000 0000">
                    </div>

                    <div class="form-group">
                        <label>Address *</label>
                        <input type="text" name="kin_address" placeholder="Emergency contact address">
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('admin.dashboard') }}" class="btn cancel">Cancel</a>
                    <button type="submit" class="btn submit">Register Patient</button>
                </div>

            </form>
            <div class="records-section">
    <div class="records-title">
        <h3>Patient Records</h3>
        <p>List of registered patients in Module 1</p>
    </div>

    <table class="records-table">
        <thead>
            <tr>
                <th>Patient No</th>
                <th>Patient Name</th>
                <th>Doctor</th>
                <th>Next of Kin</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

@forelse($patients as $patient)
<tr>
    <td>{{ $patient->patient_no }}</td>
    <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
    <td>{{ $patient->doctor_name }}</td>
    <td>{{ $patient->nok_name }}</td>
    <td>{{ $patient->phone_no }}</td>
    <td>{{ $patient->gender }}</td>
    <td>{{ $patient->status }}</td>
    <td>
        <a href="{{ route('patients.edit', $patient->patient_no) }}" class="edit-btn">Edit</a>
    </td>
</tr>

@empty

<tr>
    <td colspan="8">No patient records yet.</td>
</tr>

@endforelse

</tbody>
    </table>
</div>

        </div>

    </main>

</div>

</body>
</html>