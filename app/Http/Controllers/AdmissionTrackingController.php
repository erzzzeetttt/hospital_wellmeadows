<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionTrackingController extends Controller
{
    public function index()
{
    $patients = DB::table('patients')
        ->where('status', '!=', 'Admitted')
        ->orderBy('patient_no')
        ->get();

    $totalAdmissions = DB::table('ward_admissions')->count();

    $currentlyAdmitted = DB::table('ward_admissions')
        ->where('status', 'Admitted')
        ->count();

    $activeAdmissions = DB::table('ward_admissions')
        ->join('patients', 'ward_admissions.patient_no', '=', 'patients.patient_no')
        ->where('ward_admissions.status', 'Admitted')
        ->select(
            'ward_admissions.*',
            'patients.first_name',
            'patients.last_name'
        )
        ->orderBy('ward_admissions.date_admitted', 'desc')
        ->get();

    $recentDischarges = DB::table('ward_admissions')
    ->join('patients', 'ward_admissions.patient_no', '=', 'patients.patient_no')
    ->where('ward_admissions.status', 'Discharged')
    ->select(
        'ward_admissions.*',
        'patients.first_name',
        'patients.last_name'
    )
    ->orderBy('ward_admissions.discharge_date', 'desc')
    ->get();

    $recentDischargesCount = DB::table('ward_admissions')
    ->where('status', 'Discharged')
    ->count();

    $pendingDischarge = DB::table('ward_admissions')
    ->where('status', 'Admitted')
    ->whereDate('expected_leave_date', '<=', now()->addDays(2))
    ->count();

    return view('module1.admissiontracking', compact(
        'patients',
        'totalAdmissions',
        'currentlyAdmitted',
        'activeAdmissions',
        'recentDischarges',
        'recentDischargesCount',
        'pendingDischarge',
    ));
}


    public function store(Request $request)
    {
        $request->validate([
            'patient_no' => 'required|exists:patients,patient_no',
            'date_admitted' => 'required|date',
            'expected_leave_date' => 'required|date|after_or_equal:date_admitted',
        ]);

        $result = DB::select(
            "SELECT fn_admit_patient(?, ?, ?) AS message",
            [
                $request->patient_no,
                $request->date_admitted,
                $request->expected_leave_date,
            ]
        );

        return redirect()
            ->route('admission-tracking.index')
            ->with('success', $result[0]->message);
    }

    public function discharge(Request $request, $admission_id)
{
    $request->validate([
        'discharge_date' => 'required|date',
    ]);

    $result = DB::select(
        "SELECT fn_discharge_patient(?, ?) AS message",
        [
            (int) $admission_id,
            $request->discharge_date,
        ]
    );

    return redirect()
        ->route('admission-tracking.index')
        ->with('success', $result[0]->message);
    }
}