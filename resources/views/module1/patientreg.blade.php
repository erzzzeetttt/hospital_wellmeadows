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


    
    <main class="container">

<nav class="module-subnav">
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

        <div class="form-card">
            <div class="form-title">
                <h3>Register New Patient</h3>
                <p>Enter patient information to create a new record</p>
            </div>

            <form action="#" method="POST">
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
                    <select name="doctor_id">
                        <option value="">Select doctor</option>
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
        </div>

    </main>

</div>

</body>
</html>