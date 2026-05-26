<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module2css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/module2css/staff-registration.css') }}">
</head>
<body>

<div class="page">

    <header class="header">
        <div>
            <h2>WellMeadows Hospital</h2>
            <p>Staff Management System</p>
        </div>
        <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
    </header>

    <nav class="sub-nav">
        <a href="{{ route('staff.index') }}" class="active">Staff Registration</a>
        <a href="{{ route('staff.ward-assignment') }}">Ward Assignment</a>
        <a href="{{ route('staff.schedule') }}">Staff Schedule</a>
    </nav>

    <main class="container">

        <div class="form-card">

            <div class="form-title">
                <h3>Edit Staff Record</h3>
                <p>Update information for {{ $staff->first_name }} {{ $staff->last_name }}</p>
            </div>

            @if ($errors->any())
                <div class="alert-error" style="margin: 20px 26px;">
                    <strong>Please fix these errors:</strong>
                    <ul style="margin-left:20px; margin-top:8px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error" style="margin: 20px 26px;">{{ session('error') }}</div>
            @endif

            <form action="{{ route('staff.update', $staff->staff_no) }}" method="POST">
                @csrf
                @method('PUT')

                <h4>Personal Details</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name"
                               value="{{ old('first_name', $staff->first_name) }}"
                               placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name"
                               value="{{ old('last_name', $staff->last_name) }}"
                               placeholder="Enter last name">
                    </div>

                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob"
                               value="{{ old('dob', $staff->dob) }}">
                    </div>

                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender">
                            <option value="">Select sex</option>
                            <option {{ old('gender', $staff->gender) == 'Male'   ? 'selected' : '' }}>Male</option>
                            <option {{ old('gender', $staff->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Telephone Number *</label>
                        <input type="text" name="phone_no"
                               value="{{ old('phone_no', $staff->phone_no) }}"
                               placeholder="e.g. 0131-334-5677">
                    </div>

                    <div class="form-group">
                        <label>NIN (National Insurance No.)</label>
                        <input type="text" name="nin"
                               value="{{ old('nin', $staff->nin) }}"
                               placeholder="e.g. WB123423D">
                    </div>

                    <div class="form-group">
                        <label>Date Registered</label>
                        <input type="date" name="date_registered"
                               value="{{ old('date_registered', $staff->date_registered ?? '') }}">
                    </div>
                </div>

                <div class="form-group full" style="margin-top: 20px;">
                    <label>Full Address *</label>
                    <input type="text" name="address"
                           value="{{ old('address', $staff->address) }}"
                           placeholder="Enter complete address">
                </div>

                <h4>Position & Role</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role_id" required>
                            <option value="">Select role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}"
                                    {{ old('role_id', $staff->role_id) == $role->role_id ? 'selected' : '' }}>
                                    {{ $role->role_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Position *</label>
                        <select name="position">
                            <option value="">Select position</option>
                            @foreach(['Charge Nurse','Staff Nurse','Nurse','Consultant','Auxiliary','Physiotherapist','Doctor'] as $pos)
                                <option {{ old('position', $staff->position) == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Current Salary *</label>
                        <input type="number" name="salary"
                               value="{{ old('salary', $staff->salary) }}"
                               placeholder="0.00" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label>Salary Scale</label>
                        <input type="text" name="salary_scale"
                               value="{{ old('salary_scale', $staff->salary_scale) }}"
                               placeholder="e.g. 1C scale">
                    </div>
                </div>

                <h4>Employment Contract</h4>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Hours per Week</label>
                        <input type="number" name="hours_per_week"
                               value="{{ old('hours_per_week', $staff->hours_per_week) }}"
                               placeholder="e.g. 37.5" step="0.5" min="0">
                    </div>

                    <div class="form-group">
                        <label>Contract Type</label>
                        <select name="contract_type">
                            <option value="">Select type</option>
                            <option {{ old('contract_type', $staff->contract_type) == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                            <option {{ old('contract_type', $staff->contract_type) == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type">
                            <option value="">Select payment</option>
                            <option {{ old('payment_type', $staff->payment_type) == 'Weekly'  ? 'selected' : '' }}>Weekly</option>
                            <option {{ old('payment_type', $staff->payment_type) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                </div>

                {{-- Build quals array: old() takes priority on validation failure, else load from DB --}}
                @php
                    $quals = old('qualifications');
                    if ($quals === null) {
                        $quals = [];
                        foreach ($qualifications as $q) {
                            $quals[] = [
                                'qualification_type' => $q->qualification_type,
                                'date_obtained'      => $q->date_obtained,
                                'institution'        => $q->institution,
                            ];
                        }
                        if (empty($quals)) {
                            $quals = [['qualification_type' => '', 'date_obtained' => '', 'institution' => '']];
                        }
                    }
                @endphp

                <h4>Qualifications</h4>
                <p style="font-size:13px; color:#64748b; margin-bottom:12px;">
                    Saving this form replaces all existing qualification records for this staff member.
                </p>

                <div id="qual-container">
                    @foreach($quals as $qi => $qual)
                    <div class="dynamic-row" id="qual-row-{{ $qi }}" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:14px; margin-bottom:10px;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Qualification Type</label>
                                <input type="text"
                                       name="qualifications[{{ $qi }}][qualification_type]"
                                       value="{{ $qual['qualification_type'] ?? '' }}"
                                       placeholder="e.g. BSc Nursing Studies">
                            </div>
                            <div class="form-group">
                                <label>Date Obtained</label>
                                <input type="date"
                                       name="qualifications[{{ $qi }}][date_obtained]"
                                       value="{{ $qual['date_obtained'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Institution</label>
                                <input type="text"
                                       name="qualifications[{{ $qi }}][institution]"
                                       value="{{ $qual['institution'] ?? '' }}"
                                       placeholder="e.g. Edinburgh University">
                            </div>
                            @if($qi > 0)
                            <div class="form-group" style="align-self:flex-end;">
                                <button type="button" class="btn cancel"
                                        onclick="removeRow('qual-row-{{ $qi }}')">
                                    Remove
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" id="add-qual-btn" class="btn cancel" style="margin-bottom:20px; font-size:13px;">
                    + Add Qualification
                </button>

                {{-- Build exps array: old() takes priority on validation failure, else load from DB --}}
                @php
                    $exps = old('experiences');
                    if ($exps === null) {
                        $exps = [];
                        foreach ($workExperiences as $e) {
                            $exps[] = [
                                'position'        => $e->position,
                                'start_date'      => $e->start_date,
                                'end_date'        => $e->end_date,
                                'organization_name' => $e->organization,
                            ];
                        }
                        if (empty($exps)) {
                            $exps = [['position' => '', 'start_date' => '', 'end_date' => '', 'organization_name' => '']];
                        }
                    }
                @endphp

                <h4>Work Experience</h4>
                <p style="font-size:13px; color:#64748b; margin-bottom:12px;">
                    Saving this form replaces all existing work experience records for this staff member.
                </p>

                <div id="exp-container">
                    @foreach($exps as $ei => $exp)
                    <div class="dynamic-row" id="exp-row-{{ $ei }}" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:14px; margin-bottom:10px;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Position Held</label>
                                <input type="text"
                                       name="experiences[{{ $ei }}][position]"
                                       value="{{ $exp['position'] ?? '' }}"
                                       placeholder="e.g. Staff Nurse">
                            </div>
                            <div class="form-group">
                                <label>Organization</label>
                                <input type="text"
                                       name="experiences[{{ $ei }}][organization_name]"
                                       value="{{ $exp['organization_name'] ?? '' }}"
                                       placeholder="e.g. Western Hospital">
                            </div>
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date"
                                       name="experiences[{{ $ei }}][start_date]"
                                       value="{{ $exp['start_date'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Finish Date</label>
                                <input type="date"
                                       name="experiences[{{ $ei }}][end_date]"
                                       value="{{ $exp['end_date'] ?? '' }}">
                            </div>
                            @if($ei > 0)
                            <div class="form-group" style="align-self:flex-end;">
                                <button type="button" class="btn cancel"
                                        onclick="removeRow('exp-row-{{ $ei }}')">
                                    Remove
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" id="add-exp-btn" class="btn cancel" style="margin-bottom:20px; font-size:13px;">
                    + Add Work Experience
                </button>

                <div class="actions">
                    <a href="{{ route('staff.index') }}" class="btn cancel">Cancel</a>
                    <button type="submit" class="btn submit">Update Staff</button>
                </div>

            </form>

        </div>

    </main>

</div>

<script>
(function () {
    let qualIndex = {{ count($quals) }};
    let expIndex  = {{ count($exps) }};

    function removeRow(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }
    window.removeRow = removeRow;

    document.getElementById('add-qual-btn').addEventListener('click', function () {
        const container = document.getElementById('qual-container');
        const div = document.createElement('div');
        div.className = 'dynamic-row';
        div.id = 'qual-row-' + qualIndex;
        div.style.cssText = 'background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:14px; margin-bottom:10px;';
        div.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label>Qualification Type</label>
                    <input type="text" name="qualifications[${qualIndex}][qualification_type]" placeholder="e.g. BSc Nursing Studies">
                </div>
                <div class="form-group">
                    <label>Date Obtained</label>
                    <input type="date" name="qualifications[${qualIndex}][date_obtained]">
                </div>
                <div class="form-group">
                    <label>Institution</label>
                    <input type="text" name="qualifications[${qualIndex}][institution]" placeholder="e.g. Edinburgh University">
                </div>
                <div class="form-group" style="align-self:flex-end;">
                    <button type="button" class="btn cancel" onclick="removeRow('qual-row-${qualIndex}')">Remove</button>
                </div>
            </div>`;
        container.appendChild(div);
        qualIndex++;
    });

    document.getElementById('add-exp-btn').addEventListener('click', function () {
        const container = document.getElementById('exp-container');
        const div = document.createElement('div');
        div.className = 'dynamic-row';
        div.id = 'exp-row-' + expIndex;
        div.style.cssText = 'background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:14px; margin-bottom:10px;';
        div.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label>Position Held</label>
                    <input type="text" name="experiences[${expIndex}][position]" placeholder="e.g. Staff Nurse">
                </div>
                <div class="form-group">
                    <label>Organization</label>
                    <input type="text" name="experiences[${expIndex}][organization_name]" placeholder="e.g. Western Hospital">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="experiences[${expIndex}][start_date]">
                </div>
                <div class="form-group">
                    <label>Finish Date</label>
                    <input type="date" name="experiences[${expIndex}][end_date]">
                </div>
                <div class="form-group" style="align-self:flex-end;">
                    <button type="button" class="btn cancel" onclick="removeRow('exp-row-${expIndex}')">Remove</button>
                </div>
            </div>`;
        container.appendChild(div);
        expIndex++;
    });
})();
</script>

</body>
</html>
