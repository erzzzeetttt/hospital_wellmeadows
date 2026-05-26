<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdmissionTrackingController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicationRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleDashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\TreatmentStaffController;
use App\Http\Controllers\WardBedManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Central dashboard — redirects to the correct role dashboard
Route::get('/dashboard', function () {
    $roleName = Auth::user()->role?->role_name;

    if ($roleName === 'Administrator') {
        return redirect()->route('admin.dashboard');
    }
    if ($roleName === 'Receptionist') {
        return redirect()->route('dashboard.receptionist');
    }
    if ($roleName === 'Charge Nurse') {
        return redirect()->route('dashboard.charge-nurse');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ── Administrator Dashboard ───────────────────────────────────────────────────
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:Administrator'])
    ->name('admin.dashboard');

// ── Role-specific Dashboards ──────────────────────────────────────────────────
Route::get('/dashboard/receptionist', [RoleDashboardController::class, 'receptionist'])
    ->middleware(['auth', 'role:Administrator,Receptionist'])
    ->name('dashboard.receptionist');

Route::get('/dashboard/charge-nurse', [RoleDashboardController::class, 'chargeNurse'])
    ->middleware(['auth', 'role:Administrator,Charge Nurse'])
    ->name('dashboard.charge-nurse');

// ── Module 1: Patient Registration & Update — Receptionist + Admin ────────────
Route::middleware(['auth', 'role:Administrator,Receptionist'])->group(function () {
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient_no}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient_no}', [PatientController::class, 'update'])->name('patients.update');
});

// ── Module 1: Medical Records, Medication & Admissions — Charge Nurse + Admin ─
Route::middleware(['auth', 'role:Administrator,Charge Nurse'])->group(function () {
    Route::get('/medication-records', [MedicationRecordController::class, 'index'])->name('medical-records.index');
    Route::post('/medical-records/store', [MedicationRecordController::class, 'store'])->name('medical-records.store');
    Route::get('/medical-records/{patient_no}', [MedicationRecordController::class, 'show'])->name('medical-records.show');
    Route::put('/medical-records/{medication_id}', [MedicationRecordController::class, 'update'])->name('medical-records.update');

    // Ward Assignment moved to Module 3 (ward-bed-management.assign-bed)
    // Route::get('/ward-assignment', function () {
    //     return view('module1.wardassignment');
    // })->name('ward-assignment.index');

    Route::get('/admission-tracking', [AdmissionTrackingController::class, 'index'])->name('admission-tracking.index');
    Route::post('/admission-tracking/store', [AdmissionTrackingController::class, 'store'])->name('admission-tracking.store');
    Route::put('/admission-tracking/{admission_id}/discharge', [AdmissionTrackingController::class, 'discharge'])->name('admission-tracking.discharge');
});

// ── Profile (all authenticated users) ────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Module 2: Staff & Department Management — Administrator only ──────────────
Route::middleware(['auth', 'role:Administrator'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff_no}', [StaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{staff_no}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff_no}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff_no}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('/staff-ward-assignment', [StaffController::class, 'wardAssignment'])->name('staff.ward-assignment');
    Route::post('/staff-ward-assignment', [StaffController::class, 'storeWardAssignment'])->name('staff.ward-assignment.store');
    Route::post('/staff-ward-assignment/{assignment_id}/end', [StaffController::class, 'endAssignment'])->name('staff.ward-assignment.end');
    Route::post('/staff-ward-transfer', [StaffController::class, 'transferWard'])->name('staff.ward-transfer');
    Route::get('/staff-schedule', [StaffController::class, 'schedule'])->name('staff.schedule');
    Route::post('/staff-rota', [StaffController::class, 'storeRota'])->name('staff.rota.store');
});

// ── Module 4: Appointments — Receptionist + Admin ────────────────────────────
Route::middleware(['auth', 'role:Administrator,Receptionist'])->group(function () {
    Route::get('/module4/appointments', [AppointmentController::class, 'index'])->name('module4.appointments');
    Route::post('/module4/appointments', [AppointmentController::class, 'store'])->name('module4.appointments.store');
    Route::patch('/module4/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('module4.appointments.cancel');
    Route::post('/module4/appointments/{appointment_id}/status', [AppointmentController::class, 'updateStatus'])->name('module4.appointments.status');
});

// ── Module 4: Treatment Recording — Administrator only ───────────────────────
Route::middleware(['auth', 'role:Administrator'])->group(function () {
    Route::get('/module4/treatment-recording', [TreatmentController::class, 'index'])->name('module4.treatmentrec');
    Route::post('/module4/treatment-recording', [TreatmentController::class, 'store'])->name('module4.treatmentrec.store');
    Route::get('/module4/treatmentrec/{id}/edit', [TreatmentController::class, 'edit'])->name('module4.treatmentrec.edit');
    Route::put('/module4/treatmentrec/{id}', [TreatmentController::class, 'update'])->name('module4.treatmentrec.update');
    Route::delete('/module4/treatment/{id}', [TreatmentController::class, 'destroy'])->name('module4.treatment.delete');
});

// ─────────────────────────────────────────────────────────────────────────────

// ── Module 3: Ward and Bed Management — Charge Nurse + Admin ─────────────────
Route::middleware(['auth', 'role:Administrator,Charge Nurse'])->group(function () {
    Route::get('/ward-bed-management', [WardBedManagementController::class, 'index'])->name('ward-bed-management.index');
    Route::get('/ward-bed-management/create', [WardBedManagementController::class, 'create'])->name('ward-bed-management.create');
    Route::get('/ward-bed-management/assign-bed', [WardBedManagementController::class, 'showAssignBed'])->name('ward-bed-management.assign-bed');
    Route::get('/ward-bed-management/bed-availability', [WardBedManagementController::class, 'bedAvailability'])->name('ward-bed-management.bed-availability');
    Route::post('/ward-bed-management/wards', [WardBedManagementController::class, 'storeWard'])->name('ward-bed-management.wards.store');
    Route::put('/ward-bed-management/wards/{id}', [WardBedManagementController::class, 'updateWard'])->name('ward-bed-management.wards.update');
    Route::delete('/ward-bed-management/wards/{id}', [WardBedManagementController::class, 'destroyWard'])->name('ward-bed-management.wards.destroy');
    Route::post('/ward-bed-management/assign-bed', [WardBedManagementController::class, 'assignBed'])->name('ward-bed-management.assign-bed.store');
    Route::post('/ward-bed-management/release-bed/{id}', [WardBedManagementController::class, 'releaseBed'])->name('ward-bed-management.release-bed');
    Route::post('/ward-bed-management/beds', [WardBedManagementController::class, 'storeBed'])->name('ward-bed-management.beds.store');
    Route::put('/ward-bed-management/beds/{id}/status', [WardBedManagementController::class, 'updateBedStatus'])->name('ward-bed-management.beds.status');
});

// ── Module 5: Billing & Reporting — Administrator only ───────────────────────
use App\Http\Controllers\BillingController;

Route::middleware(['auth', 'role:Administrator'])->group(function () {
    Route::get('/billing',                  [BillingController::class, 'index'])         ->name('billing.index');
    Route::get('/billing/create',           [BillingController::class, 'create'])        ->name('billing.create');
    Route::post('/billing',                 [BillingController::class, 'store'])         ->name('billing.store');
    Route::post('/billing/payment',         [BillingController::class, 'storePayment'])  ->name('billing.storePayment');
    Route::get('/billing/{id}',             [BillingController::class, 'show'])          ->name('billing.show');
    Route::get('/billing/{id}/payment',     [BillingController::class, 'recordPayment']) ->name('billing.payment');
    Route::post('/billing/{id}/cancel',     [BillingController::class, 'cancel'])        ->name('billing.cancel');
    Route::get('/reports',                  [BillingController::class, 'reports'])       ->name('billing.reports');
});

// ─────────────────────────────────────────────────────────────────────────────

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

require __DIR__.'/auth.php';
