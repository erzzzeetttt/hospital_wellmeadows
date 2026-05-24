<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdmissionTrackingController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicationRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\TreatmentStaffController;
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

// Ward Assignment moved to Module 3 (ward-bed-management.assign-bed)
// Route::get('/ward-assignment', function () {
//     return view('module1.wardassignment');
// })->middleware('auth')->name('ward-assignment.index');

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

// Module 4: Appointment & Treatment
Route::get('/module4/appointments', [AppointmentController::class, 'index'])
    ->middleware(['auth'])->name('module4.appointments');
Route::post('/module4/appointments', [AppointmentController::class, 'store'])
    ->middleware(['auth'])->name('module4.appointments.store');
Route::patch('/module4/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])
    ->middleware(['auth'])->name('module4.appointments.cancel');
Route::post('/module4/appointments/{appointment_id}/status', [AppointmentController::class, 'updateStatus'])
    ->middleware(['auth'])->name('module4.appointments.status');

Route::get('/module4/treatment-recording', [TreatmentController::class, 'index'])
    ->middleware(['auth'])->name('module4.treatmentrec');
Route::post('/module4/treatment-recording', [TreatmentController::class, 'store'])
    ->middleware(['auth'])->name('module4.treatmentrec.store');

Route::get('/module4/treatmentrec/{id}/edit', [TreatmentController::class, 'edit'])
    ->middleware(['auth'])->name('module4.treatmentrec.edit');
Route::put('/module4/treatmentrec/{id}', [TreatmentController::class, 'update'])
    ->middleware(['auth'])->name('module4.treatmentrec.update');
Route::delete('/module4/treatment/{id}', [TreatmentController::class, 'destroy'])
    ->middleware(['auth'])->name('module4.treatment.delete');

// ─────────────────────────────────────────────────────────────────────────────

// Module 3: Ward and Bed Management
Route::get('/ward-bed-management', [WardBedManagementController::class, 'index'])
    ->middleware('auth')
    ->name('ward-bed-management.index');

Route::get('/ward-bed-management/create', [WardBedManagementController::class, 'create'])
    ->middleware('auth')
    ->name('ward-bed-management.create');

Route::get('/ward-bed-management/assign-bed', [WardBedManagementController::class, 'showAssignBed'])
    ->middleware('auth')
    ->name('ward-bed-management.assign-bed');

Route::get('/ward-bed-management/bed-availability', [WardBedManagementController::class, 'bedAvailability'])
    ->middleware('auth')
    ->name('ward-bed-management.bed-availability');

Route::post('/ward-bed-management/wards', [WardBedManagementController::class, 'storeWard'])
    ->middleware('auth')
    ->name('ward-bed-management.wards.store');

Route::put('/ward-bed-management/wards/{id}', [WardBedManagementController::class, 'updateWard'])
    ->middleware('auth')
    ->name('ward-bed-management.wards.update');

Route::delete('/ward-bed-management/wards/{id}', [WardBedManagementController::class, 'destroyWard'])
    ->middleware('auth')
    ->name('ward-bed-management.wards.destroy');

Route::post('/ward-bed-management/assign-bed', [WardBedManagementController::class, 'assignBed'])
    ->middleware('auth')
    ->name('ward-bed-management.assign-bed.store');

Route::post('/ward-bed-management/release-bed/{id}', [WardBedManagementController::class, 'releaseBed'])
    ->middleware('auth')
    ->name('ward-bed-management.release-bed');

Route::post('/ward-bed-management/beds', [WardBedManagementController::class, 'storeBed'])
    ->middleware('auth')
    ->name('ward-bed-management.beds.store');

Route::put('/ward-bed-management/beds/{id}/status', [WardBedManagementController::class, 'updateBedStatus'])
    ->middleware('auth')
    ->name('ward-bed-management.beds.status');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

require __DIR__.'/auth.php';
