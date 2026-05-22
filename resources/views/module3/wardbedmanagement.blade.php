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
        <input type="radio" name="ward-tab" id="tab-all-wards" checked>
        <input type="radio" name="ward-tab" id="tab-add-ward">
        <input type="radio" name="ward-tab" id="tab-assign-bed">
        <input type="radio" name="ward-tab" id="tab-bed-availability">

        <nav class="ward-tabs" aria-label="Ward management sections">
            <label for="tab-all-wards">All Wards</label>
            <label for="tab-add-ward">Add Ward</label>
            <label for="tab-assign-bed">Assign Bed</label>
            <label for="tab-bed-availability">Bed Availability</label>
        </nav>

        <section class="tab-panel all-wards-panel">
            <div class="content-card">
                <div class="card-header">
                    <h2>Available Wards</h2>
                    <p>Total of <strong>0</strong> wards registered in the system</p>
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
                            <tr>
                                <td colspan="9" class="empty-cell">No wards have been added yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="tab-panel add-ward-panel">
            <form class="content-card form-card">
                <div class="card-header">
                    <h2>Add New Ward</h2>
                    <p>Enter ward details to register a new ward in the system</p>
                </div>

                <div class="form-section">
                    <h3>Ward Information</h3>

                    <div class="form-grid">
                        <label>
                            <span>Ward Name <em>*</em></span>
                            <input type="text" placeholder="e.g. Grampian">
                        </label>

                        <label>
                            <span>Ward Type <em>*</em></span>
                            <select>
                                <option>Select ward type</option>
                                <option>Orthopaedic</option>
                                <option>Cardiology</option>
                                <option>General Surgery</option>
                            </select>
                        </label>

                        <label>
                            <span>Location <em>*</em></span>
                            <input type="text" placeholder="e.g. Block A, Floor 2">
                        </label>

                        <label>
                            <span>Total Bed Capacity <em>*</em></span>
                            <input type="number" placeholder="e.g. 12">
                        </label>
                    </div>
                </div>

                <div class="form-section contact-section">
                    <h3>Contact Details</h3>

                    <div class="form-grid">
                        <label>
                            <span>Charge Nurse <em>*</em></span>
                            <input type="text" placeholder="Full name">
                        </label>

                        <label>
                            <span>Phone / Extension</span>
                            <input type="text" placeholder="e.g. ext. 2201">
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-light">Clear</button>
                    <button type="button" class="btn btn-primary">Add Ward</button>
                </div>
            </form>
        </section>

        <section class="tab-panel assign-bed-panel">
            <form class="content-card form-card">
                <div class="card-header">
                    <h2>Assign Bed to Patient</h2>
                    <p>Select a ward and available bed to assign to an admitted patient</p>
                </div>

                <div class="form-section">
                    <h3>Bed Selection</h3>

                    <div class="form-grid">
                        <label>
                            <span>Ward <em>*</em></span>
                            <select>
                                <option>Select a ward</option>
                            </select>
                        </label>

                        <label>
                            <span>Available Bed <em>*</em></span>
                            <select>
                                <option>Select a bed</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="form-section contact-section">
                    <h3>Patient Details</h3>

                    <div class="form-grid">
                        <label>
                            <span>Patient ID <em>*</em></span>
                            <input type="text" placeholder="e.g. PT-0041">
                        </label>

                        <label>
                            <span>Patient Full Name <em>*</em></span>
                            <input type="text" placeholder="Enter full name">
                        </label>

                        <label>
                            <span>Consulting Doctor</span>
                            <input type="text" placeholder="e.g. Dr. R. Kinnaird">
                        </label>

                        <label>
                            <span>Admission Date</span>
                            <input type="date">
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-light">Clear</button>
                    <button type="button" class="btn btn-primary">Assign Bed</button>
                </div>
            </form>
        </section>

        <section class="tab-panel bed-availability-panel">
            <div class="stats-grid">
                <article class="stat-card total">
                    <span>Total Beds</span>
                    <strong>0</strong>
                </article>
                <article class="stat-card vacant-bg">
                    <span>Vacant</span>
                    <strong>0</strong>
                </article>
                <article class="stat-card occupied-bg">
                    <span>Occupied</span>
                    <strong>0</strong>
                </article>
                <article class="stat-card maintenance-bg">
                    <span>Maintenance</span>
                    <strong>0</strong>
                </article>
            </div>

            <div class="content-card availability-card">
                <div class="availability-header">
                    <div>
                        <h2>Bed Availability by Ward</h2>
                        <p>View and filter bed status across all wards</p>
                    </div>

                    <label class="ward-filter">
                        <span>Ward:</span>
                        <select>
                            <option>All Wards</option>
                        </select>
                    </label>
                </div>

                <div class="empty-availability">No bed availability records to display yet.</div>
            </div>
        </section>
    </main>
</body>
</html>
