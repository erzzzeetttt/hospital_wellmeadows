<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdmissionTrackingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicationRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WardBedManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (Auth::user()->role_id == 1) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::get('/patients/create', [PatientController::class, 'create'])
    ->middleware('auth')
    ->name('patients.create');

Route::post('/patients', [PatientController::class, 'store'])
    ->middleware('auth')
    ->name('patients.store');

Route::get('/patients/{patient_no}/edit', [PatientController::class, 'edit'])
    ->middleware('auth')
    ->name('patients.edit');

Route::put('/patients/{patient_no}', [PatientController::class, 'update'])
    ->middleware('auth')
    ->name('patients.update');

Route::get('/medication-records', [MedicationRecordController::class, 'index'])
    ->middleware('auth')
    ->name('medical-records.index');

Route::post('/medical-records/store', [MedicationRecordController::class, 'store'])
    ->middleware('auth')
    ->name('medical-records.store');

Route::get('/medical-records/{patient_no}', [MedicationRecordController::class, 'show'])
    ->middleware('auth')
    ->name('medical-records.show');

Route::put('/medical-records/{medication_id}', [MedicationRecordController::class, 'update'])
    ->middleware('auth')
    ->name('medical-records.update');

Route::get('/ward-assignment', function () {
    return view('module1.wardassignment');
})->middleware('auth')->name('ward-assignment.index');

Route::get('/admission-tracking', [AdmissionTrackingController::class, 'index'])
    ->middleware('auth')
    ->name('admission-tracking.index');

Route::post('/admission-tracking/store', [AdmissionTrackingController::class, 'store'])
    ->middleware('auth')
    ->name('admission-tracking.store');

Route::put('/admission-tracking/{admission_id}/discharge', [AdmissionTrackingController::class, 'discharge'])
    ->middleware('auth')
    ->name('admission-tracking.discharge');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Module 2: Staff & Department Management ──────────────────────────────────
Route::get('/staff', [StaffController::class, 'index'])
    ->middleware('auth')
    ->name('staff.index');

Route::post('/staff', [StaffController::class, 'store'])
    ->middleware('auth')
    ->name('staff.store');

Route::get('/staff/{staff_no}', [StaffController::class, 'show'])
    ->middleware('auth')
    ->name('staff.show');

Route::get('/staff/{staff_no}/edit', [StaffController::class, 'edit'])
    ->middleware('auth')
    ->name('staff.edit');

Route::put('/staff/{staff_no}', [StaffController::class, 'update'])
    ->middleware('auth')
    ->name('staff.update');

Route::delete('/staff/{staff_no}', [StaffController::class, 'destroy'])
    ->middleware('auth')
    ->name('staff.destroy');

Route::get('/staff-ward-assignment', [StaffController::class, 'wardAssignment'])
    ->middleware('auth')
    ->name('staff.ward-assignment');

Route::post('/staff-ward-assignment', [StaffController::class, 'storeWardAssignment'])
    ->middleware('auth')
    ->name('staff.ward-assignment.store');

Route::get('/staff-schedule', [StaffController::class, 'schedule'])
    ->middleware('auth')
    ->name('staff.schedule');

Route::post('/staff-rota', [StaffController::class, 'storeRota'])
    ->middleware('auth')
    ->name('staff.rota.store');
// ─────────────────────────────────────────────────────────────────────────────

// Module 3: Ward and Bed Management
Route::get('/ward-bed-management', [WardBedManagementController::class, 'index'])
    ->middleware('auth')
    ->name('ward-bed-management.index');

Route::post('/ward-bed-management/wards', [WardBedManagementController::class, 'storeWard'])
    ->middleware('auth')
    ->name('ward-bed-management.wards.store');

Route::post('/ward-bed-management/assign-bed', [WardBedManagementController::class, 'assignBed'])
    ->middleware('auth')
    ->name('ward-bed-management.assign-bed.store');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

require __DIR__.'/auth.php';
