<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Treatment Record</title>
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
                    <h3>Edit Treatment Record</h3>
                    <p>Diagnosis ID: {{ $treatment->diagnosis_id }} &mdash; {{ $treatment->patient_name }}</p>
                </div>

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

                <form method="POST" action="{{ route('module4.treatmentrec.update', $treatment->diagnosis_id) }}">
                    @csrf
                    @method('PUT')

                    <section class="date-controls">
                        <div>
                            <h4>Patient Information</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Patient No</label>
                                    <input type="text" value="{{ $treatment->patient_no }}" readonly
                                        style="background:#f8fafc;color:#64748b;">
                                </div>
                                <div class="form-group">
                                    <label>Patient Name</label>
                                    <input type="text" value="{{ $treatment->patient_name }}" readonly
                                        style="background:#f8fafc;color:#64748b;">
                                </div>
                                <div class="form-group">
                                    <label>Diagnosis Date</label>
                                    <input type="date" name="diagnosis_date"
                                        value="{{ old('diagnosis_date', $treatment->diagnosis_date) }}" required>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="date-controls">
                        <div>
                            <h4>Diagnosis / Findings</h4>
                            <div class="form-group">
                                <label>Diagnosis Details *</label>
                                <textarea name="diagnosis_details" rows="5" required
                                    placeholder="Enter diagnosis or clinical findings">{{ old('diagnosis_details', $treatment->diagnosis_details) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="date-controls">
                        <div>
                            <h4>Treatment Details</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Treatment Type *</label>
                                    <select name="treatment_type" required>
                                        <option value="">Select type</option>
                                        <option value="Medication" {{ old('treatment_type', $treatment->treatment_type) == 'Medication' ? 'selected' : '' }}>Medication</option>
                                        <option value="Surgical" {{ old('treatment_type', $treatment->treatment_type) == 'Surgical' ? 'selected' : '' }}>Surgical</option>
                                        <option value="Non-Surgical" {{ old('treatment_type', $treatment->treatment_type) == 'Non-Surgical' ? 'selected' : '' }}>Non-Surgical</option>
                                        <option value="Diagnosis Only" {{ old('treatment_type', $treatment->treatment_type) == 'Diagnosis Only' ? 'selected' : '' }}>Diagnosis Only</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Method</label>
                                    <select name="method">
                                        <option value="">Select method</option>
                                        <option value="Oral" {{ old('method', $treatment->method) == 'Oral' ? 'selected' : '' }}>Oral</option>
                                        <option value="Intravenous (IV)" {{ old('method', $treatment->method) == 'Intravenous (IV)' ? 'selected' : '' }}>Intravenous (IV)</option>
                                        <option value="Topical" {{ old('method', $treatment->method) == 'Topical' ? 'selected' : '' }}>Topical</option>
                                        <option value="Injection" {{ old('method', $treatment->method) == 'Injection' ? 'selected' : '' }}>Injection</option>
                                        <option value="Inhalation" {{ old('method', $treatment->method) == 'Inhalation' ? 'selected' : '' }}>Inhalation</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Treatment Given</label>
                                    <textarea name="treatment_given" rows="4"
                                        placeholder="Describe treatment provided">{{ old('treatment_given', $treatment->treatment_given) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Remarks / Notes</label>
                                    <textarea name="remarks" rows="4"
                                        placeholder="Additional notes">{{ old('remarks', $treatment->remarks) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="summary-section">
                        <div>
                            <h4>Actions</h4>
                        </div>
                        <div class="date-actions">
                            <a href="{{ route('module4.treatmentrec') }}" class="btn cancel">Cancel</a>
                            <button type="submit" class="btn submit">Save Changes</button>
                        </div>
                    </section>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
