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

    <a href="{{ route('patients.create') }}" class="active">
        Patient Registration
    </a>

    <a href="#">
        Medical Records
    </a>

    <a href="#">
        Ward Assignment
    </a>

    <a href="#">
        Admission Tracking
    </a>

</nav>

    <main class="container">

        <div class="form-card">
            <div class="form-title">
                <h3>Register New Patient</h3>
                <p>Enter patient information to create a new record</p>
            </div>
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

    @if (session('success'))
    <div style="background:#dcfce7; color:#166534; padding:12px 20px; margin:20px 26px; border-radius:6px;">
        {{ session('success') }}
    </div>
@endif

            <form action="{{ route('patients.update', $patient->patient_no) }}" method="POST">
            @csrf
            @method('PUT')

                <h4>Personal Information</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text"
                        name="first_name"
                        value="{{ old('first_name', $patient->first_name) }}"
                        placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $patient->last_name) }}" placeholder="Enter last name">
                    </div>

                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth) }}">
                    </div>

                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender">
                            <option value="">Select gender</option>
                            <option {{ old('gender', $patient->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option {{ old('gender', $patient->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="phone_no" value="{{ old('phone_no', $patient->phone_no) }}" placeholder="+63 900 000 0000">
                    </div>

                    <div class="form-group">
                        <label>Marital Status</label>
                        <select name="marital_status">
                            <option value="">Select status</option>
                            <option {{ old('marital_status', $patient->marital_status) == 'Single' ? 'selected' : '' }}>Single</option>
                            <option {{ old('marital_status', $patient->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                            <option {{ old('marital_status', $patient->marital_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            <option {{ old('marital_status', $patient->marital_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Address *</label>
                    <input type="text" name="address" value="{{ old('address', $patient->address) }}" placeholder="Enter complete address">
                </div>

               <h4>Doctor Assignment</h4>

<div class="form-grid">
    <div class="form-group">
        <label>Local Doctor *</label>
        <select name="doctor_id" required>
            <option value="">Select doctor</option>

            @foreach($doctors as $doctor)
                <option value="{{ $doctor->doctor_id }}"
                    {{ old('doctor_id', $patient->doctor_id) == $doctor->doctor_id ? 'selected' : '' }}>
                    {{ $doctor->fullname }}
                </option>
            @endforeach
        </select>
    </div>

                <div class="form-group">
         <label>Status</label>
    <input type="text" value="{{ $patient->status }}" readonly>
</div>
</div>

                <h4>Next of Kin Information</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="kin_fullname" value="{{ old('kin_fullname', $nextOfKin->fullname) }}" placeholder="Emergency contact name">
                    </div>

                    <div class="form-group">
                        <label>Relationship *</label>
                        <input type="text" name="relationshiptopatient" value="{{ old('relationshiptopatient', $nextOfKin->relationshiptopatient) }}" placeholder="e.g. Mother, Father">
                    </div>

                    <div class="form-group">
                        <label>Telephone *</label>
                        <input type="text" name="kin_telno" value="{{ old('kin_telno', $nextOfKin->telno) }}" placeholder="+63 900 000 0000">
                    </div>

                    <div class="form-group">
                        <label>Address *</label>
                        <input type="text" name="kin_address" value="{{ old('kin_address', $nextOfKin->address) }}" placeholder="Emergency contact address">
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('patients.create') }}" class="btn cancel">Cancel</a>
                    <button type="submit" class="btn submit">Update Patient</button>
                </div>

            </form>
            
</body>
</html>