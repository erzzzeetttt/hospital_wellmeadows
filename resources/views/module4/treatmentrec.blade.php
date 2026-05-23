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
            <a href="{{ route('module4.treatmentrec') }}">Treatment Recording</a>
            <a href="{{ route('module4.treatmenthistory') }}">Treatment History</a>
            <a href="{{ route('module4.staffassign') }}">Staff Assignment</a>
        </nav>

        <main class="container">
            <div class="form-card">
                <div class="form-title">
                    <h3>Treatment Recording</h3>
                    <p>Record patient treatment details for a completed appointment</p>
                </div>

                <form method="POST" action="#">
                    @csrf

                    <section class="date-controls">
                        <div>
                            <h4>Patient Information</h4>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Select Patient</label>
                                    <select name="patient_no">
                                        <option value="">Select patient</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Attending Doctor</label>
                                    <select name="staff_no">
                                        <option value="">Select doctor</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Diagnosis Date</label>
                                    <input type="date" name="diagnosis_date">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="date-controls">
                        <div>
                            <h4>Treatment Details</h4>

                            <div class="form-group">
                                <label>Diagnosis / Findings</label>
                                <textarea 
                                    name="diagnosis_details"
                                    placeholder="Enter diagnosis or clinical findings"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="date-controls">
                        <div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Treatment Given</label>
                                    <textarea name="treatment_given" rows="4" placeholder="Describe treatment provided"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Remarks / Notes</label>
                                    <textarea name="remarks" rows="4" placeholder="Additional notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="summary-section">
                        <div>
                            <h4>Actions</h4>
                            <p>Save the diagnosis record or clear the form before entering a new record.</p>
                        </div>

                        <div class="date-actions">
                            <button type="submit" class="btn submit">Save Record</button>
                            <button type="reset" class="btn cancel">Clear Form</button>
                        </div>
                    </section>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
